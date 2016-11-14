<?php
/**
 * @package Simplicate
 */
/*
Plugin Name: Simplicate CRM
Plugin URI: http://simplicate.nl
Description: Om de website te koppelen aan Simplicate heb je allereerst natuurlijk een Simplicate account nodig. Mocht je dit nog niet hebben, dan kan je dit hier vrijblijvend aanvragen. De eerste 14 dagen zijn gewoon gratis! Jouw bestaande formulieren kun je vervolgens zelf (of door de webbouwer van jouw website) uitbreiden met een koppeling naar Simplicate via Wordpress.
Version: 1.0.1+BETA
Author: Simplicate Software B.V.
Author URI: http://simplicate.nl/wordpress-plugins/
License: MIT
Text Domain: simplicate
*/

define('SIMPLICATE_VERSION', '0.1');
define('SIMPLICATE__MINIMUM_WP_VERSION', '4.6');
define('SIMPLICATE__PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, ['Simplicate', 'plugin_activation']);
register_deactivation_hook( __FILE__, ['Simplicate', 'plugin_deactivation']);

require_once( SIMPLICATE__PLUGIN_DIR . 'class.simplicate.php' );
require_once( SIMPLICATE__PLUGIN_DIR . 'src/helper.php' );
// Gravity Forms Plugin
require_once( SIMPLICATE__PLUGIN_DIR . 'gravityformssimplicatecrm/simplicatecrm.php');
//require_once( SIMPLICATE__PLUGIN_DIR . '../gravityformsagilecrm/agilecrm.php');

add_action( 'init', array( 'Simplicate', 'init' ) );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( SIMPLICATE__PLUGIN_DIR . 'src/SimplicateAdmin.php' );
    add_action( 'init', array( 'SimplicateAdmin', 'init' ) );
}
