<?php
/**
 *
 * @package   WCISPlugin
 * @license   GPL-2.0+
 * @copyright 2014 InstantSearchPlus
 *
 * @wordpress-plugin
 * Plugin Name:       InstantSearch+ for WooCommerce
 * Plugin URI:        www.instantsearchplus.com 
 * Description:       Best search plugin for WooCommerce
 * Version:           1.0.18
 * Author:            Fast Simon Inc
 * Author URI:        www.instantsearchplus.com
 * Text Domain:       plugin-name-locale
 * License:           GPL-2.0+
 * Domain Path:       /languages
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/wcis_plugin.php' );


register_activation_hook( __FILE__, array( 'WCISPlugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WCISPlugin', 'deactivate' ) );

register_uninstall_hook( __FILE__, array( 'WCISPlugin', 'uninstall' ) );



add_action( 'plugins_loaded', array( 'WCISPlugin', 'get_instance' ) );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'WCISPlugin', 'wcis_add_action_links'));

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/WCISPluginAdmin.php' );
	add_action( 'plugins_loaded', array( 'WCISPluginAdmin', 'get_instance' ) );

}
?>