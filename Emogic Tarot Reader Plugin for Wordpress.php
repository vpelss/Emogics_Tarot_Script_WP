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
    define( 'EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME' , __file__ ); // c:\*********\EMOGIC_TAROT_pulgin_folder\Emogic Tarot Reader Plugin for Wordpress.php
    define( 'EMOGIC_TAROT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // c:\************\Emogic Tarot Reader Plugin for Wordpress/
    
    define( 'EMOGIC_TAROT_PLUGIN_LOCATION_URL', plugins_url('/' , __FILE__) ); // http://wp_url/wp-content/plugins/Emogic Tarot Reader Plugin for Wordpress/
    define( 'EMOGIC_TAROT_PLUGIN_NAME' , plugin_basename( __FILE__ ) ); // Emogic Tarot Reader Plugin for Wordpress (or other if renamed)
    define( 'EMOGIC_TAROT_PLUGIN_WP_ROOT_URL' , home_url() ); // http://wp_url/
    //same as folder structure under /pages/ in plugin
    define( 'EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER' , "emogic-databases" ); // will be emogic_databases 
    define( 'EMOGIC_TAROT_PLUGIN_READING_FOLDER' , "emogic-readings" ); // will be emogic_readings
    define( 'EMOGIC_TAROT_PLUGIN_EMAIL_TEMPLATE_FOLDER' , "emogic-reading-email-template" ); // will be emogic_readings
    define( 'EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER' , "|" ); // delimeter in flat file db 
    define( 'EMOGIC_TAROT_PLUGIN_MEDIA_FOLDER' , "Emogic-Images" ); // Emogic-Images
 
     register_activation_hook( EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME , ['EmogicTarotReader_Plugin' , 'activate'] );
     register_deactivation_hook( EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME , ['EmogicTarotReader_Plugin' , 'deactivate'] );
     add_action("admin_init", ["EmogicTarotReader_Plugin" , "enqueue_js_admin"] );
     
class EmogicTarotReader_Plugin {
    
    public static function activate() {
        require_once EMOGIC_TAROT_PLUGIN_PATH . 'inc/EmogicTarotReader_Activator.php';
        EmogicTarotReader_Activator::activate();
    }
    
    public static function deactivate() {
        require_once EMOGIC_TAROT_PLUGIN_PATH . 'inc/EmogicTarotReader_Deactivator.php';
        EmogicTarotReader_Deactivator::deactivate();
    }

   public static function enqueue_js_admin(){
       wp_enqueue_script('ETSWP__wp-js_admin', EMOGIC_TAROT_PLUGIN_LOCATION_URL . 'js/ETSWP_js_admin.js');
   }
     
}

if( is_admin() ){
    require_once EMOGIC_TAROT_PLUGIN_PATH . 'admin/EmogicTarotReader_Admin.php';
    EmogicTarotReader_Admin::init(); //set up admin option(s)
    }  // for some reason wp calculates shortcodes on edit screens causing errors even though it will not display them
else{
    require_once EMOGIC_TAROT_PLUGIN_PATH . 'inc/EmogicTarotReader_Core.php' ; 
    EmogicTarotReader_Core::init();
}
