<?php
/*
Plugin Name: Youneeq Recommendations
Plugin URI: http://www.youneeq.ca/
Description: Integrates Youneeq's industry-leading recommendation engine into your site. With Youneeq's recommendations, your users will see links to the best articles your site or network has to offer. If you are a Youneeq customer, all you have to do is customize the Youneeq panel's behavior in the YQ Settings page and place the widget on your site.
Version: 3.0.6
Author: Youneeq
Author URI: http://www.youneeq.ca/
License: MIT
License URI: http://opensource.org/licenses/mit-license.html
Text Domain: youneeq-panel
*/

/**
 * youneeq-panel: plugin loader file
 *
 * @package   Youneeq\WP_Recs
 * @version   3.0.6
 * @since     3.0.0
 * @link      https://wordpress.org/plugins/youneeq-panel/
 * @author    Alex Smith
 * @license   http://opensource.org/licenses/mit-license.html MIT
 * @copyright 2018 Youneeq
 */

// Check to see if PHP version is too old
if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
    if ( function_exists( 'add_action' ) ) {
        add_action( 'admin_notices', create_function( '', 'echo \'<div class="notice notice-error"><p>' . __( 'Youneeq Recommendations requires PHP 5.4 or newer. Please upgrade PHP or deactivate the plugin.', 'youneeq-panel' ) . '</p></div>\';' ) );
    }

    return;
}
else {
    /**
     * Main plugin class file.
     *
     * @see Yqr_Main
     */
    require_once 'class/main.php';

    // Register special hooks.
    $base_name = plugin_basename( __FILE__ );
    register_activation_hook( $base_name, 'Yqr_Main::activate' );
    register_deactivation_hook( $base_name, 'Yqr_Main::deactivate' );
    register_uninstall_hook( $base_name, 'Yqr_Main::uninstall' );

    // Initialize plugin.
    if ( !defined( 'WP_UNINSTALL_PLUGIN' ) && function_exists( 'add_action' ) ) {
        Yqr_Main::init( __DIR__, plugins_url( dirname( $base_name ) ), $base_name, is_admin() );
    }
}

/* end */