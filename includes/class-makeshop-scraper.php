<?php
/**
 * Makeshop Product Scraper Class
 *
 * @package Preview_And_Tools_For_Makeshop_WP_Option
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class for scraping makeshop product information.
 */
class TFMWP_Makeshop_Scraper {

	/**
	 * Cache expiration time in seconds (1 hour).
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = 3600;

	/**
	 * Convert CSS selector to XPath.
	 *
	 * @param string $css_selector The CSS selector.
	 * @return string The XPath expression.
	 */
	private static function css_to_xpath( $css_selector ) {
		// Trim whitespace.
		$css_selector = trim( $css_selector );

		// Handle multiple selectors separated by comma (OR).
		if ( strpos( $css_selector, ',' ) !== false ) {
			$selectors = array_map( 'trim', explode( ',', $css_selector ) );
			$xpath_parts = array();
			foreach ( $selectors as $selector ) {
				$xpath_parts[] = self::css_to_xpath( $selector );
			}
			return implode( ' | ', $xpath_parts );
		}

		// Handle attribute-only selectors for getting attribute values.
		// e.g., "meta[property='og:image']::attr(content)" → "//meta[@property='og:image']/@content"
		if ( preg_match( '/(.+)::attr\(([^)]+)\)$/', $css_selector, $matches ) ) {
			$element_selector = $matches[1];
			$attr_name = $matches[2];
			$xpath = self::css_to_xpath_single( $element_selector );
			return $xpath . '/@' . $attr_name;
		}

		return self::css_to_xpath_single( $css_selector );
	}

	/**
	 * Convert single CSS selector to XPath (no comma separation).
	 *
	 * @param string $css The CSS selector.
	 * @return string The XPath expression.
	 */
	private static function css_to_xpath_single( $css ) {
		$xpath = '//';

		// Replace descendant combinator (space) with //
		// But we need to handle it carefully to not break attribute selectors.
		$parts = preg_split( '/\s+/', $css );

		$xpath_parts = array();
		foreach ( $parts as $part ) {
			$xpath_parts[] = self::css_part_to_xpath( $part );
		}

		$xpath .= implode( '//', $xpath_parts );

		return $xpath;
	}

	/**
	 * Convert a single CSS selector part to XPath.
	 *
	 * @param string $css_part The CSS selector part.
	 * @return string The XPath expression.
	 */
	private static function css_part_to_xpath( $css_part ) {
		$xpath = '';

		// Match element with id: div#myid → div[@id="myid"]
		if ( preg_match( '/^([a-z0-9]+)?#([a-z0-9_-]+)/i', $css_part, $matches ) ) {
			$element = ! empty( $matches[1] ) ? $matches[1] : '*';
			$id = $matches[2];
			return $element . '[@id="' . $id . '"]';
		}

		// Match element with class: div.myclass or .myclass → div[contains(@class, "myclass")]
		if ( preg_match( '/^([a-z0-9]+)?\.([a-z0-9_-]+)/i', $css_part, $matches ) ) {
			$element = ! empty( $matches[1] ) ? $matches[1] : '*';
			$class = $matches[2];
			return $element . '[contains(@class, "' . $class . '")]';
		}

		// Match attribute selectors: [attr="value"] or element[attr="value"]
		if ( preg_match( '/^([a-z0-9]+)?\[([a-z0-9_-]+)(?:([=~|^$*])="?([^"\]]+)"?)?\]/i', $css_part, $matches ) ) {
			$element = ! empty( $matches[1] ) ? $matches[1] : '*';
			$attr = $matches[2];
			$operator = isset( $matches[3] ) ? $matches[3] : '';
			$value = isset( $matches[4] ) ? $matches[4] : '';

			if ( empty( $operator ) ) {
				// [attr] - attribute exists
				return $element . '[@' . $attr . ']';
			} elseif ( $operator === '=' ) {
				// [attr="value"] - exact match
				return $element . '[@' . $attr . '="' . $value . '"]';
			} elseif ( $operator === '*' ) {
				// [attr*="value"] - contains
				return $element . '[contains(@' . $attr . ', "' . $value . '")]';
			} elseif ( $operator === '^' ) {
				// [attr^="value"] - starts with
				return $element . '[starts-with(@' . $attr . ', "' . $value . '")]';
			}
		}

		// Just an element name.
		return $css_part;
	}

	/**
	 * Fetch product information from makeshop URL.
	 *
	 * @param string $url The product URL.
	 * @param bool   $force_refresh Force refresh cache.
	 * @return array|WP_Error Product information or error.
	 */
	public static function fetch_product( $url, $force_refresh = false ) {
		if ( empty( $url ) ) {
			return new WP_Error( 'empty_url', __( 'Product URL is required.', 'tools-for-makeshop-wp-option' ) );
		}

		// Generate cache key from URL.
		$cache_key = 'tfmwp_product_' . md5( $url );

		// Try to get from cache unless force refresh.
		if ( ! $force_refresh ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fetch HTML from URL.
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return new WP_Error( 'empty_response', __( 'Failed to fetch product information.', 'tools-for-makeshop-wp-option' ) );
		}

		// Parse HTML and extract product information.
		$product = self::parse_product_html( $html, $url );

		if ( is_wp_error( $product ) ) {
			return $product;
		}

		// Cache the result.
		set_transient( $cache_key, $product, self::CACHE_EXPIRATION );

		return $product;
	}

	/**
	 * Parse HTML and extract product information.
	 *
	 * @param string $html The HTML content.
	 * @param string $url The product URL.
	 * @return array|WP_Error Product information or error.
	 */
	private static function parse_product_html( $html, $url ) {
		// Suppress warnings from DOMDocument.
		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );

		libxml_clear_errors();

		$xpath = new DOMXPath( $dom );

		$product = array(
			'url'         => $url,
			'image'       => '',
			'name'        => '',
			'category'    => '',
			'price'       => '',
			'description' => '',
		);

		// Get custom selectors from settings (CSS format), or use defaults.
		$css_selector_name = get_option( 'tfmwp_selector_name', '' );
		if ( empty( $css_selector_name ) ) {
			$css_selector_name = 'h1.item_name, h1.product-name, meta[property=og:title]::attr(content)';
		}

		$css_selector_image = get_option( 'tfmwp_selector_image', '' );
		if ( empty( $css_selector_image ) ) {
			$css_selector_image = 'meta[property=og:image]::attr(content), .product-image img::attr(src), img.item_image::attr(src)';
		}

		$css_selector_category = get_option( 'tfmwp_selector_category', '' );
		if ( empty( $css_selector_category ) ) {
			$css_selector_category = '.breadcrumb a, .category';
		}

		$css_selector_price = get_option( 'tfmwp_selector_price', '' );
		if ( empty( $css_selector_price ) ) {
			$css_selector_price = '.price, meta[property=product:price:amount]::attr(content)';
		}

		$css_selector_description = get_option( 'tfmwp_selector_description', '' );
		if ( empty( $css_selector_description ) ) {
			$css_selector_description = 'meta[property=og:description]::attr(content), meta[name=description]::attr(content)';
		}

		// Convert CSS selectors to XPath.
		$selector_name = self::css_to_xpath( $css_selector_name );
		$selector_image = self::css_to_xpath( $css_selector_image );
		$selector_category = self::css_to_xpath( $css_selector_category );
		$selector_price = self::css_to_xpath( $css_selector_price );
		$selector_description = self::css_to_xpath( $css_selector_description );

		// Extract product name.
		$name_nodes = $xpath->query( $selector_name );
		if ( $name_nodes && $name_nodes->length > 0 ) {
			$product['name'] = trim( $name_nodes->item( 0 )->nodeValue );
		}

		// Extract product image.
		$image_nodes = $xpath->query( $selector_image );
		if ( $image_nodes && $image_nodes->length > 0 ) {
			$product['image'] = trim( $image_nodes->item( 0 )->nodeValue );
		}

		// Extract category.
		$category_nodes = $xpath->query( $selector_category );
		if ( $category_nodes && $category_nodes->length > 0 ) {
			$product['category'] = trim( $category_nodes->item( 0 )->nodeValue );
		}

		// Extract price.
		$price_nodes = $xpath->query( $selector_price );
		if ( $price_nodes && $price_nodes->length > 0 ) {
			$product['price'] = trim( $price_nodes->item( 0 )->nodeValue );
		}

		// Extract description.
		$desc_nodes = $xpath->query( $selector_description );
		if ( $desc_nodes && $desc_nodes->length > 0 ) {
			$product['description'] = trim( $desc_nodes->item( 0 )->nodeValue );
		}

		// Validate that we got at least some data.
		if ( empty( $product['name'] ) && empty( $product['image'] ) ) {
			return new WP_Error( 'parse_error', __( 'Failed to parse product information from the page.', 'tools-for-makeshop-wp-option' ) );
		}

		return $product;
	}

	/**
	 * Clear cache for a specific product URL.
	 *
	 * @param string $url The product URL.
	 * @return bool True on success, false on failure.
	 */
	public static function clear_cache( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$cache_key = 'tfmwp_product_' . md5( $url );
		return delete_transient( $cache_key );
	}
}
