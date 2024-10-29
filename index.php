<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
Plugin Name: YYDevelopment - Advanced 301 and 302 Redirect
Plugin URI:  https://www.yydevelopment.com/yydevelopment-wordpress-plugins/
Description: Simple plugin that will allow to redirect pages as 301 and 302 redirects in wordpress
Version:     1.6.8
Author:      YYDevelopment
Author URI:  https://www.yydevelopment.com/
*/

include('include/settings.php');
require_once('include/functions.php');

$yydev_redirect_data_plugin_version = '1.5.0'; // plugin version
$yydev_redirect_data_slug_name = 'yydev_advanced_301_redirect_version'; // the name we save on the wp_options database

// ================================================
// Creating Database when the plugin is activated
// ================================================

function yydev_redirect_create_redirection_database() {
    require_once('include/install.php');
} 

register_activation_hook(__FILE__, 'yydev_redirect_create_redirection_database');

// ================================================
// update the database on plugin update
// ================================================

// loading the plugin version from the database
$db_plugin_version = get_option($yydev_redirect_data_slug_name);

// checking if the plugin version exists on the dabase
// and checking if the database version equal to the plugin version $yydev_redirect_data_plugin_version
if( empty($db_plugin_version) || ($yydev_redirect_data_plugin_version != $db_plugin_version) ) {

    // update the plugin database if it's required
    $yydev_redirect_database_update = 1;
    require_once('include/install.php');

    // update the plugin version in the database
    update_option($yydev_redirect_data_slug_name, $yydev_redirect_data_plugin_version);

} // if( empty($db_plugin_version) || ($yydev_redirect_data_plugin_version != $db_plugin_version) ) {

// add_action('plugins_loaded', 'my_awesome_plugin_check_version');


// ================================================
// Adding menu tag inside wordpress admin panel
// ================================================

function yydev_redirect_wordpress_redirect_page() {

    include('include/settings.php');

    include('include/style.php');
    include('include/script.php');
    
    // Including the main page and the secondary page
    if( isset($_GET['view']) && ($_GET['view'] === 'secondary') && isset($_GET['id'])  ) {
        include('include/secondary-page.php');
    } else {
        include('include/main-page.php');
    }
    
} 

function yydev_redirect_plugin_menu() {
    include('include/settings.php');
    add_options_page('301/302 Redirection', '301/302 Redirection', 'manage_options', 'yydev-redirection', 'yydev_redirect_wordpress_redirect_page');
}

add_action('admin_menu', 'yydev_redirect_plugin_menu');

// ================================================
// Add settings page to the plugin menu info
// ================================================

function yydev_redirect_add_settings_link( $actions, $plugin_file ) {
	static $plugin;

    if (!isset($plugin)) { $plugin = plugin_basename(__FILE__); }
    
	if ($plugin == $plugin_file) {

            $admin_page_url = esc_url( menu_page_url( 'yydev-redirection', false ) );
			$settings = array('settings' => '<a href="' . $admin_page_url . '">Settings</a>');
            $donate = array('donate' => '<a target="_blank" href="https://www.yydevelopment.com/coffee-break/?plugin=advanced-301-and-302-redirect">Donate</a>');
		
            $actions = array_merge($settings, $donate, $actions);
        
    } // if ($plugin == $plugin_file) {
		
    return $actions;
} //function yydev_redirect_add_settings_link( $actions, $plugin_file ) {

add_filter( 'plugin_action_links', 'yydev_redirect_add_settings_link', 10, 5 );

// ================================================
// Include the redirect functions only if the theme
// is loaded and not on the admin panel area
// ================================================

if ( ! is_admin() ) {
    require_once('include/redirect-page.php');
} // if ( ! is_admin() ) {

// ================================================
// including admin notices flie
// ================================================

if( is_admin() ) {
	include_once('notices.php');
} // if( is_admin() ) {