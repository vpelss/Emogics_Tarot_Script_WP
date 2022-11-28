<?php
/**
* Plugin Name: Emogic Tarot Reader for WP
* Plugin URI: https://github.com/vpelss/EMOGIC_TAROT_wp
* Description: Emogic Tarot Reader for WP.
* Version: 0.9
* Author: Vince Pelss
* Author URI: https://www.emogic.com/
**/

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

// define variable for path to this plugin file.
define( 'EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME' , __file__ ); // c:\*********\EMOGIC_TAROT_pulgin_folder\imok.php
define( 'EMOGIC_TAROT_PLUGIN_PATH', dirname( __FILE__ ) ); // c:\************\EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_LOCATION_URL', plugins_url( '', __FILE__ ) ); // http://wp_url/wp-content/plugins/EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_NAME' , plugin_basename( __FILE__ ) ); // EMOGIC_TAROT_wp (or other if renamed)
define( 'EMOGIC_TAROT_ROOT_URL' , home_url() ); // http://wp_url/

register_activation_hook( __FILE__, 'activate_ETSWP' );
function activate_ETSWP() {
	require_once plugin_dir_path( __FILE__ ) . 'inc/activate.php';
	ETSWP_activator::activate();
}

register_deactivation_hook( __FILE__, 'deactivate_ETSWP' );
function deactivate_ETSWP() {
	require_once plugin_dir_path( __FILE__ ) . 'inc/deactivate.php';
	ETSWP_deactivator::deactivate();
}

require_once plugin_dir_path(__file__) . 'inc/ETSWP.php' ; //
function run_ETSWP() {
	$plugin = new ETSWP();
	$plugin->run();
}
run_ETSWP();

//setup instructions, use possibilities, etc

//more spreads

//backup/restore spreads?
//deck edit page?
//backup/restore decks

//reading deck, fix blank lines, etc...

//Numbers Speak reading

//tarot, rune, other folder (all with main index page) to keeps decks separate?
