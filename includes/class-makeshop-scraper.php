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

		// Extract product name.
		$name_nodes = $xpath->query( '//h1[@class="item_name"] | //h1[contains(@class, "product-name")] | //meta[@property="og:title"]/@content' );
		if ( $name_nodes->length > 0 ) {
			$product['name'] = trim( $name_nodes->item( 0 )->nodeValue );
		}

		// Extract product image.
		$image_nodes = $xpath->query( '//meta[@property="og:image"]/@content | //div[contains(@class, "product-image")]//img/@src | //img[@class="item_image"]/@src' );
		if ( $image_nodes->length > 0 ) {
			$product['image'] = trim( $image_nodes->item( 0 )->nodeValue );
		}

		// Extract category.
		$category_nodes = $xpath->query( '//div[contains(@class, "breadcrumb")]//a[last()-1] | //span[@class="category"]' );
		if ( $category_nodes->length > 0 ) {
			$product['category'] = trim( $category_nodes->item( 0 )->nodeValue );
		}

		// Extract price.
		$price_nodes = $xpath->query( '//span[contains(@class, "price")] | //div[contains(@class, "price")] | //meta[@property="product:price:amount"]/@content' );
		if ( $price_nodes->length > 0 ) {
			$product['price'] = trim( $price_nodes->item( 0 )->nodeValue );
		}

		// Extract description.
		$desc_nodes = $xpath->query( '//meta[@property="og:description"]/@content | //meta[@name="description"]/@content' );
		if ( $desc_nodes->length > 0 ) {
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
