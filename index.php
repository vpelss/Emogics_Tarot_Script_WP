<?php
/**
* Plugin Name: Emogic Tarot Reader for WP
* Plugin URI: https://github.com/vpelss/EMOGIC_TAROT_wp
* Description: Emogic Tarot Reader for WP.
* Version: 0.1
* Author: Vince Pelss
* Author URI: https://www.emogic.com/
**/

// Enable WP_DEBUG mode
//define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
//define( 'WP_DEBUG_LOG', true );

/* exit if directly accessed */
if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

// define variable for path to this plugin file.
define( 'EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME' , __file__ ); // c:\*********\EMOGIC_TAROT_pulgin_folder\imok.php
define( 'EMOGIC_TAROT_PLUGIN_PATH', dirname( __FILE__ ) ); // c:\************\EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_LOCATION_URL', plugins_url( '', __FILE__ ) ); // http://wp_url/wp-content/plugins/EMOGIC_TAROT_pulgin_folder
define( 'EMOGIC_TAROT_PLUGIN_NAME' , plugin_basename( __FILE__ ) ); // EMOGIC_TAROT_wp (or other if renamed)
define( 'EMOGIC_TAROT_ROOT_URL' , home_url() ); // http://wp_url/

class emogic_tarot {//keep variables and routines from going wp global

	function __construct() {

		require_once plugin_dir_path(__file__) . 'inc/activate.php' ; //set up pages : class
		require_once plugin_dir_path(__file__) . 'inc/deactivate.php' ; //remove created pages : class
		//require_once plugin_dir_path(__file__) . 'inc/enqueue.php' ;//add js and styles : class
		//require_once plugin_dir_path(__file__) . 'inc/admin.php' ;//add admin page (?empty) , settings links , MOVE TO imok/settings add meta type , user fields , user field write

	}

	function activate(){

	}

	function deactivate(){

	}

	function uninstall(){

	}

	function init(){

		//act similar to subroutines
		require_once plugin_dir_path(__file__) . 'inc/pages.php' ; //auto setup pages

//stop here if not on one of our pages

		require_once plugin_dir_path(__file__) . 'inc/shuffle.php' ; //main page redirects to page based on status

		//require_once plugin_dir_path(__file__) . 'inc/login_logout.php' ; //logging in logging out page functions
		//require_once plugin_dir_path(__file__) . 'inc/settings.php' ; //settings page functions
		//require_once plugin_dir_path(__file__) . 'inc/cron.php' ; //cron page functions
		//require_once plugin_dir_path(__file__) . 'inc/commands.php' ; //functions for IMOK Logged In page

		$r = 9;
	}

}

if( class_exists('emogic_tarot') ){
	$emogic_tarot = new emogic_tarot();
	//add_action( 'wp_loaded', array($emogic_tarot, 'init') );
	}

$emogic_tarot->init();


//The plugin generated 2302 characters of unexpected output during activation. If you notice “headers already sent” messages, problems with syndication feeds or other issues, try deactivating or removing this plugin.

//name and question on reading page

//setup instructions, use possibilities, etc

//text one line

//more spreads

//backup/restore spreads?
//deck edit page?
//backup/restore decks

//reading deck, fix blank lines, etc...

//Numbers Speak reading

//tarot, rune, other folder (all with main index page) to keeps decks separate?

?>
