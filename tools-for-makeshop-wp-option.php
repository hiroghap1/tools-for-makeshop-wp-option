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
 * Initialize the plugin.
 */
function tfmwp_init() {
	// Plugin initialization code here
}
add_action( 'plugins_loaded', 'tfmwp_init' );

/**
 * Replace preview post link for makeshop reverse proxy.
 *
 * Replaces home_url() with site_url() in preview links to ensure
 * the block editor preview works correctly on makeshop's reverse proxy.
 *
 * @param string $url The preview post link URL.
 * @return string Modified URL.
 */
function tfmwp_replace_preview_post_link( $url ) {
	$url = str_replace( home_url(), site_url(), $url );
	return $url;
}
add_filter( 'preview_post_link', 'tfmwp_replace_preview_post_link' );

/**
 * Replace REST API URL for makeshop reverse proxy.
 *
 * Replaces home_url() with site_url() in REST URLs when in admin area
 * to ensure the block editor can save and preview correctly on makeshop's reverse proxy.
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
add_filter( 'rest_url', 'tfmwp_replace_rest_url' );