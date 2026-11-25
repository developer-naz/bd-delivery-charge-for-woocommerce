<?php
/**
 * Plugin Name:          BD Delivery Charge for WooCommerce
 * Plugin URI:           https://github.com/developer-naz/bd-delivery-charge-for-woocommerce
 * Description:          A WooCommerce shipping method for Bangladesh: Inside Dhaka, Dhaka sub-area and Outside Dhaka delivery charges, per-district rates and free delivery above an amount.
 * Version:              1.0.0
 * Author:               Md Nazmul Islam (developer-naz)
 * Author URI:           https://www.linkedin.com/in/developernaz/
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          bddc
 * Requires at least:    6.0
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 8.0
 *
 * @package BDDC
 */

defined( 'ABSPATH' ) || exit;

define( 'BDDC_VERSION', '1.0.0' );
define( 'BDDC_FILE', __FILE__ );
define( 'BDDC_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', BDDC_FILE, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', BDDC_FILE, true );
	}
} );

/**
 * Register the shipping method once WooCommerce's shipping classes are loaded.
 */
add_action( 'woocommerce_shipping_init', function () {
	require_once BDDC_PATH . 'includes/class-bddc-shipping-method.php';
} );

add_filter( 'woocommerce_shipping_methods', function ( $methods ) {
	$methods['bd_delivery_charge'] = 'BDDC_Shipping_Method';
	return $methods;
} );

/**
 * "Add shipping zone" shortcut on the Plugins screen.
 */
add_filter( 'plugin_action_links_' . plugin_basename( BDDC_FILE ), function ( $links ) {
	array_unshift(
		$links,
		'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . '">' . esc_html__( 'Shipping zones', 'bddc' ) . '</a>'
	);
	return $links;
} );
