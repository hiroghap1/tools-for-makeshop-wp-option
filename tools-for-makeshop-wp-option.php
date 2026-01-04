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
		null,
		'tfmwp-settings'
	);

	add_settings_field(
		'tfmwp_enable_product_block',
		__( 'Enable Product Display Block', 'tools-for-makeshop-wp-option' ),
		'tfmwp_enable_product_block_callback',
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