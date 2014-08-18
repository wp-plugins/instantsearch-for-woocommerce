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
 * Version:           1.2.4
 * Author:            Fast Simon Inc
 * Author URI:        www.instantsearchplus.com
 * Text Domain:       WCISPlugin
 * License:           GPL-2.0+
 * Domain Path:       /languages
  */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once(plugin_dir_path( __FILE__ ) . 'public/wcis_plugin.php');
require_once(plugin_dir_path( __FILE__ ) . 'widget/instantsearch-for-woocommerce-widget.php');

register_activation_hook( __FILE__, array( WCISPlugin::get_instance(), 'activate' ) );
register_deactivation_hook( __FILE__, array( WCISPlugin::get_instance(), 'deactivate' ) );

register_uninstall_hook( __FILE__, array( 'WCISPlugin', 'uninstall' ) );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( 'WCISPlugin', 'wcis_add_action_links'));

?>