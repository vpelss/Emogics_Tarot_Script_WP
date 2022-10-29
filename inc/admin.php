<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//namespace inc\admin;

class admin{

//set an admin page and put it in wp admin left menu
	static function add_admin_pages(){
		//add_menu_page( 'browser tab text' , 'link text' , 'manage_options' , 'url ? page name' , 'path to html inc\admin\admin::admin_index' , 'dashicons-store' , 110 );
		//add_menu_page( 'imok Plugin' , 'IMOK Admin' , 'manage_options' , 'EMOGIC_TAROT_plugin' , 'admin::admin_index' , 'dashicons-store' , 110 );
	}

	static function admin_index(){//generates html output
		require_once EMOGIC_TAROT_PLUGIN_PATH . '/templates/admin.php'; //
	}

	//set up link under plugin on plugin page
	static function settings_link($links){
		//add custom settings link
		$settings_link = '<a href="admin.php?page=EMOGIC_TAROT_plugin">Settings</a>';
		array_push($links , $settings_link);
		return $links;
	}

} //end of admin class

//disable admin bar for users
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
		}
	}

add_action( 'admin_menu' , array('admin' ,'add_admin_pages') ); //set an admin page and put it in wp admin left menu
add_filter( "plugin_action_links_" . EMOGIC_TAROT_PLUGIN_NAME , 'admin::settings_link' ); 	//set up link under plugin on plugin page

//the imok fields to be added to user profile page
//these require echo to feed to user page at correct time. return goes to a bit bucket so it does not work here
add_action( 'show_user_profile', 'EMOGIC_TAROT_settings_form_echo' ); // Add the imok fields to user's own profile editing screen
add_action( 'edit_user_profile', 'EMOGIC_TAROT_settings_form_echo' ); // Add the imok fields to user profile editing screen for admins
function EMOGIC_TAROT_settings_form_echo( $user ){
	$html = EMOGIC_TAROT_settings_form($user);
	$html = "<h2 id='settings_top'>IMOK Data Settings Below:</h2><hr>" . $html;
	echo $html;
	}

add_action( 'personal_options_update', 'EMOGIC_TAROT_process_form' ); // allows user to update IMOK settings in their account page
add_action( 'edit_user_profile_update', 'EMOGIC_TAROT_process_form' ); // allows admin to update IMOK settings


?>
