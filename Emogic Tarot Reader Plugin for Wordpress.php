<?php
/**
* Plugin Name: Emogic Tarot Reader Plugin for Wordpress
* Plugin URI: https://github.com/vpelss/Emogics_Tarot_Script_WP#readme
* Description: Emogic Tarot Reader Plugin for Wordpress
* Version: 0.8.0
* License: GPLv3
* License URI: https://github.com/vpelss/Emogics_Tarot_Script_WP?tab=GPL-3.0-1-ov-file#readme
* Author: Vince Pelss
* Author URI: https://www.emogic.com/
**/

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

// define variable for path to this plugin file.
define( 'EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME' , __file__ ); // c:\*********\EMOGIC_TAROT_pulgin_folder\imok.php
define( 'EMOGIC_TAROT_PLUGIN_PATH', dirname( __FILE__ ) ); // c:\************\EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_LOCATION_URL', plugins_url( '', __FILE__ ) ); // http://wp_url/wp-content/plugins/EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_NAME' , plugin_basename( __FILE__ ) ); // EMOGIC_TAROT_wp (or other if renamed)
define( 'EMOGIC_TAROT_PLUGIN_WP_ROOT_URL' , home_url() ); // http://wp_url/
//same as folder structure under /pages/ in plugin
define( 'EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER' , "emogic-databases" ); // will be emogic_databases 
define( 'EMOGIC_TAROT_PLUGIN_READING_FOLDER' , "emogic-readings" ); // will be emogic_readings
define( 'EMOGIC_TAROT_PLUGIN_EMAIL_TEMPLATE_FOLDER' , "emogic-reading-email-template" ); // will be emogic_readings
define( 'EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER' , "|" ); // delimeter in flat file db 

register_activation_hook( __FILE__, 'EmogicTarotReader_activate' );
function EmogicTarotReader_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'inc/EmogicTarotReader_Activator.php';
	EmogicTarotReader_Activator::activate();
}

register_deactivation_hook( __FILE__, 'EmogicTarotReader_deactivate' );
function EmogicTarotReader_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'inc/EmogicTarotReader_Deactivator.php';
	EmogicTarotReader_Deactivator::deactivate();
}

if( is_admin() ){
	return;
	}  // no need for any of this on an admin. for some reason wp calculates shortcode on edit screens causing errors

require_once plugin_dir_path(__file__) . 'inc/EmogicTarotReader_Core.php' ; //
function EmogicTarotReader__run() {
	$plugin = new EmogicTarotReader_Core();
	$plugin->run();
}
EmogicTarotReader__run();

