<?php
/**
 * Plugin Name: Preview and Tools for makeshop WordPress option
 * Description: makeshopのWordPress連携オプション用のプレビューとツール
 * Version: 0.0.1
 * Author: HASEGAWA Yoshihiro
 * Text Domain: tools-for-makeshop-wp-option
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package Preview_And_Tools_For_Makeshop_WP_Option
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'TFMWP_VERSION', '0.0.1' );

/**
 * Plugin base path.
 */
define( 'TFMWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin base URL.
 */
define( 'TFMWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin activation hook.
 */
function tfmwp_activate() {
	// Activation code here
}
register_activation_hook( __FILE__, 'tfmwp_activate' );

/**
 * Plugin deactivation hook.
 */
function tfmwp_deactivate() {
	// Deactivation code here
}
register_deactivation_hook( __FILE__, 'tfmwp_deactivate' );

/**
 * Load plugin textdomain for translations.
 */
function tfmwp_load_textdomain() {
	load_plugin_textdomain(
		'tools-for-makeshop-wp-option',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'tfmwp_load_textdomain' );

/**
 * Load required files.
 */
require_once TFMWP_PLUGIN_DIR . 'includes/class-makeshop-scraper.php';
require_once TFMWP_PLUGIN_DIR . 'includes/class-block-product-display.php';

/**
 * Initialize the plugin.
 */
function tfmwp_init() {
	// Add admin menu
	add_action( 'admin_menu', 'tfmwp_add_admin_menu' );
	add_action( 'admin_init', 'tfmwp_register_settings' );

	// Enable preview features if setting is enabled
	if ( tfmwp_is_preview_enabled() ) {
		add_filter( 'preview_post_link', 'tfmwp_replace_preview_post_link' );
		add_filter( 'rest_url', 'tfmwp_replace_rest_url' );
	}

	// Initialize product display block if enabled
	if ( tfmwp_is_product_block_enabled() ) {
		TFMWP_Block_Product_Display::init();
	}
}
add_action( 'plugins_loaded', 'tfmwp_init' );

/**
 * Add admin menu for plugin settings.
 */
function tfmwp_add_admin_menu() {
	add_menu_page(
		__( 'makeshop Tools Settings', 'tools-for-makeshop-wp-option' ),
		__( 'makeshop Tools', 'tools-for-makeshop-wp-option' ),
		'manage_options',
		'tfmwp-settings',
		'tfmwp_render_settings_page',
		'dashicons-admin-generic',
		81
	);
}

/**
 * Register plugin settings.
 */
function tfmwp_register_settings() {
	register_setting(
		'tfmwp_settings_group',
		'tfmwp_enable_preview',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_enable_product_block',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	// Register product display field settings.
	register_setting(
		'tfmwp_settings_group',
		'tfmwp_display_image',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_display_category',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_display_price',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_display_description',
		array(
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'tfmwp_sanitize_checkbox',
		)
	);

	// Register line clamp settings.
	register_setting(
		'tfmwp_settings_group',
		'tfmwp_name_line_clamp',
		array(
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_description_line_clamp',
		array(
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	// Register selector settings.
	register_setting(
		'tfmwp_settings_group',
		'tfmwp_selector_name',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_selector_image',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_selector_category',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_selector_price',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	register_setting(
		'tfmwp_settings_group',
		'tfmwp_selector_description',
		array(
			'type'              => 'string',
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	add_settings_section(
		'tfmwp_main_section',
		__( 'Preview Settings', 'tools-for-makeshop-wp-option' ),
		null,
		'tfmwp-settings'
	);

	add_settings_field(
		'tfmwp_enable_preview',
		__( 'Enable Preview Feature', 'tools-for-makeshop-wp-option' ),
		'tfmwp_enable_preview_callback',
		'tfmwp-settings',
		'tfmwp_main_section'
	);

	add_settings_section(
		'tfmwp_block_section',
		__( 'Block Settings', 'tools-for-makeshop-wp-option' ),
		'tfmwp_block_section_callback',
		'tfmwp-settings'
	);

	add_settings_field(
		'tfmwp_enable_product_block',
		__( 'Enable Product Display Block', 'tools-for-makeshop-wp-option' ),
		'tfmwp_enable_product_block_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	// Product Name Settings (always displayed).
	add_settings_field(
		'tfmwp_selector_name',
		__( 'Product Name Selector', 'tools-for-makeshop-wp-option' ),
		'tfmwp_selector_name_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_name_line_clamp',
		__( 'Product Name Line Limit', 'tools-for-makeshop-wp-option' ),
		'tfmwp_name_line_clamp_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	// Product Image Settings.
	add_settings_field(
		'tfmwp_selector_image',
		__( 'Product Image Selector', 'tools-for-makeshop-wp-option' ),
		'tfmwp_selector_image_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_display_image',
		__( 'Display Product Image', 'tools-for-makeshop-wp-option' ),
		'tfmwp_display_image_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	// Category Settings.
	add_settings_field(
		'tfmwp_selector_category',
		__( 'Category Selector', 'tools-for-makeshop-wp-option' ),
		'tfmwp_selector_category_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_display_category',
		__( 'Display Category', 'tools-for-makeshop-wp-option' ),
		'tfmwp_display_category_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	// Price Settings.
	add_settings_field(
		'tfmwp_selector_price',
		__( 'Price Selector', 'tools-for-makeshop-wp-option' ),
		'tfmwp_selector_price_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_display_price',
		__( 'Display Price', 'tools-for-makeshop-wp-option' ),
		'tfmwp_display_price_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	// Description Settings.
	add_settings_field(
		'tfmwp_selector_description',
		__( 'Description Selector', 'tools-for-makeshop-wp-option' ),
		'tfmwp_selector_description_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_display_description',
		__( 'Display Description', 'tools-for-makeshop-wp-option' ),
		'tfmwp_display_description_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);

	add_settings_field(
		'tfmwp_description_line_clamp',
		__( 'Description Line Limit', 'tools-for-makeshop-wp-option' ),
		'tfmwp_description_line_clamp_callback',
		'tfmwp-settings',
		'tfmwp_block_section'
	);
}

/**
 * Sanitize checkbox value.
 *
 * @param mixed $value The checkbox value.
 * @return bool Sanitized boolean value.
 */
function tfmwp_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Render enable preview field.
 */
function tfmwp_enable_preview_callback() {
	$enabled = get_option( 'tfmwp_enable_preview', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_enable_preview" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Enable block editor preview and save functionality on WordPress integration option', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	</td></tr></tbody></table>
	<hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
	<?php
}

/**
 * Render block section callback.
 */
function tfmwp_block_section_callback() {
	?>
	<p><?php esc_html_e( 'Configure product display block settings. CSS selectors are used to retrieve product information from makeshop sites.', 'tools-for-makeshop-wp-option' ); ?></p>
	<p><strong><?php esc_html_e( 'CSS Selector Examples:', 'tools-for-makeshop-wp-option' ); ?></strong></p>
	<ul style="list-style-type: disc; margin-left: 20px;">
		<li><code>h1.product-title</code> - <?php esc_html_e( 'Element with class', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>.price</code> - <?php esc_html_e( 'Any element with class', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>#product-name</code> - <?php esc_html_e( 'Element with ID', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>meta[property=og:image]::attr(content)</code> - <?php esc_html_e( 'Get attribute value', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>.price, .product-price</code> - <?php esc_html_e( 'Multiple selectors (OR)', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>div.product img::attr(src)</code> - <?php esc_html_e( 'Nested elements', 'tools-for-makeshop-wp-option' ); ?></li>
	</ul>
	<?php
}

/**
 * Render enable product block field.
 */
function tfmwp_enable_product_block_callback() {
	$enabled = get_option( 'tfmwp_enable_product_block', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_enable_product_block" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Enable makeshop product display block in the block editor', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	<?php
}

/**
 * Render display image field.
 */
function tfmwp_display_image_callback() {
	$enabled = get_option( 'tfmwp_display_image', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_display_image" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Show product images', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	<?php
}

/**
 * Render display category field.
 */
function tfmwp_display_category_callback() {
	$enabled = get_option( 'tfmwp_display_category', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_display_category" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Show product categories', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	<?php
}

/**
 * Render display price field.
 */
function tfmwp_display_price_callback() {
	$enabled = get_option( 'tfmwp_display_price', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_display_price" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Show product prices', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	<?php
}

/**
 * Render display description field.
 */
function tfmwp_display_description_callback() {
	$enabled = get_option( 'tfmwp_display_description', true );
	?>
	<label>
		<input type="checkbox" name="tfmwp_display_description" value="1" <?php checked( $enabled, true ); ?> />
		<?php esc_html_e( 'Show product descriptions', 'tools-for-makeshop-wp-option' ); ?>
	</label>
	<?php
}

/**
 * Render name line clamp field.
 */
function tfmwp_name_line_clamp_callback() {
	$value = get_option( 'tfmwp_name_line_clamp', 0 );
	?>
	<input type="number" name="tfmwp_name_line_clamp" value="<?php echo esc_attr( $value ); ?>" min="0" max="10" step="1" style="width: 80px;" />
	<p class="description">
		<?php esc_html_e( 'Number of lines to display for product name. Set to 0 for no limit.', 'tools-for-makeshop-wp-option' ); ?>
	</p>
	<?php
}

/**
 * Render description line clamp field.
 */
function tfmwp_description_line_clamp_callback() {
	$value = get_option( 'tfmwp_description_line_clamp', 0 );
	?>
	<input type="number" name="tfmwp_description_line_clamp" value="<?php echo esc_attr( $value ); ?>" min="0" max="10" step="1" style="width: 80px;" />
	<p class="description">
		<?php esc_html_e( 'Number of lines to display for product description. Set to 0 for no limit.', 'tools-for-makeshop-wp-option' ); ?>
	</p>
	<?php
}

/**
 * Render selector section description.
 */
function tfmwp_selector_section_callback() {
	?>
	<p><?php esc_html_e( 'Configure custom CSS selectors for retrieving product information from makeshop sites. Leave empty to use default selectors.', 'tools-for-makeshop-wp-option' ); ?></p>
	<p><strong><?php esc_html_e( 'CSS Selector Examples:', 'tools-for-makeshop-wp-option' ); ?></strong></p>
	<ul style="list-style-type: disc; margin-left: 20px;">
		<li><code>h1.product-title</code> - <?php esc_html_e( 'Element with class', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>.price</code> - <?php esc_html_e( 'Any element with class', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>#product-name</code> - <?php esc_html_e( 'Element with ID', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>meta[property=og:image]::attr(content)</code> - <?php esc_html_e( 'Get attribute value', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>.price, .product-price</code> - <?php esc_html_e( 'Multiple selectors (OR)', 'tools-for-makeshop-wp-option' ); ?></li>
		<li><code>div.product img::attr(src)</code> - <?php esc_html_e( 'Nested elements', 'tools-for-makeshop-wp-option' ); ?></li>
	</ul>
	<?php
}

/**
 * Render product name selector field.
 */
function tfmwp_selector_name_callback() {
	$value = get_option( 'tfmwp_selector_name', '' );
	$default = 'h1.item_name, h1.product-name, meta[property=og:title]::attr(content)';
	?>
	<input type="text" name="tfmwp_selector_name" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Default:', 'tools-for-makeshop-wp-option' ); ?>
		<code><?php echo esc_html( $default ); ?></code>
	</p>
	<?php
}

/**
 * Render product image selector field.
 */
function tfmwp_selector_image_callback() {
	$value = get_option( 'tfmwp_selector_image', '' );
	$default = 'meta[property=og:image]::attr(content), .product-image img::attr(src), img.item_image::attr(src)';
	?>
	<input type="text" name="tfmwp_selector_image" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Default:', 'tools-for-makeshop-wp-option' ); ?>
		<code><?php echo esc_html( $default ); ?></code>
	</p>
	<?php
}

/**
 * Render category selector field.
 */
function tfmwp_selector_category_callback() {
	$value = get_option( 'tfmwp_selector_category', '' );
	$default = '.breadcrumb a, .category';
	?>
	<input type="text" name="tfmwp_selector_category" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Default:', 'tools-for-makeshop-wp-option' ); ?>
		<code><?php echo esc_html( $default ); ?></code>
	</p>
	<?php
}

/**
 * Render price selector field.
 */
function tfmwp_selector_price_callback() {
	$value = get_option( 'tfmwp_selector_price', '' );
	$default = '.price, meta[property=product:price:amount]::attr(content)';
	?>
	<input type="text" name="tfmwp_selector_price" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Default:', 'tools-for-makeshop-wp-option' ); ?>
		<code><?php echo esc_html( $default ); ?></code>
	</p>
	<?php
}

/**
 * Render description selector field.
 */
function tfmwp_selector_description_callback() {
	$value = get_option( 'tfmwp_selector_description', '' );
	$default = 'meta[property=og:description]::attr(content), meta[name=description]::attr(content)';
	?>
	<input type="text" name="tfmwp_selector_description" value="<?php echo esc_attr( $value ); ?>" class="large-text" placeholder="<?php echo esc_attr( $default ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Default:', 'tools-for-makeshop-wp-option' ); ?>
		<code><?php echo esc_html( $default ); ?></code>
	</p>
	<?php
}

/**
 * Render settings page.
 */
function tfmwp_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'makeshop Tools Settings', 'tools-for-makeshop-wp-option' ); ?></h1>
		<p><?php esc_html_e( 'Configure preview settings for WordPress integration option.', 'tools-for-makeshop-wp-option' ); ?></p>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'tfmwp_settings_group' );
			do_settings_sections( 'tfmwp-settings' );
			submit_button( __( 'Save Settings', 'tools-for-makeshop-wp-option' ) );
			?>
		</form>
	</div>
	<?php
}

/**
 * Check if preview feature is enabled.
 *
 * @return bool True if preview is enabled, false otherwise.
 */
function tfmwp_is_preview_enabled() {
	return (bool) get_option( 'tfmwp_enable_preview', true );
}

/**
 * Check if product display block is enabled.
 *
 * @return bool True if product block is enabled, false otherwise.
 */
function tfmwp_is_product_block_enabled() {
	return (bool) get_option( 'tfmwp_enable_product_block', true );
}

/**
 * Replace preview post link for WordPress integration option.
 *
 * Replaces home_url() with site_url() in preview links to ensure
 * the block editor preview works correctly on WordPress integration option.
 *
 * @param string $url The preview post link URL.
 * @return string Modified URL.
 */
function tfmwp_replace_preview_post_link( $url ) {
	$url = str_replace( home_url(), site_url(), $url );
	return $url;
}

/**
 * Replace REST API URL for WordPress integration option.
 *
 * Replaces home_url() with site_url() in REST URLs when in admin area
 * to ensure the block editor can save and preview correctly on WordPress integration option.
 *
 * @param string $url The REST API URL.
 * @return string Modified URL.
 */
function tfmwp_replace_rest_url( $url ) {
	if ( is_admin() ) {
		$url = str_replace( home_url(), site_url(), $url );
	}
	return $url;
}