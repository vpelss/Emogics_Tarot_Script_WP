<?php

class EmogicTarotReader_Admin {

	public static function run() {
		//set up setting link in plugin menu
		add_filter( "plugin_action_links_" . EMOGIC_TAROT_PLUGIN_NAME , 'EmogicTarotReader_Admin::settings_link' ); 
		//set up admin form field(s)
		add_action( 'admin_init', 'EmogicTarotReader_Admin::settings_init' ); //admin_init is triggered before any other hook when a user accesses the admin area.
		//page title , menu title , capability , menu_slug , callback
		add_action( 'admin_menu', 'EmogicTarotReader_Admin::add_admin_menu' );
		//add_options_page( 'Emogic Tarot Reader Settings 2', 'ETSWP Settings', 'manage_options', 'ETSWP_settings2', 'EmogicTarotReader_Admin::imok_settings_section_callback' ); 
	}
	
	
	public static function add_admin_menu(  ) {
		//add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int $position = null ):
		add_options_page( 'Emogic Tarot Reader Settings 2', 'ETSWP', 'manage_options', 'ETSWP_settings', 'EmogicTarotReader_Admin::options_page' ); 
		//add_options_page( 'imok settings', 'imok', 'manage_options', 'imok_settings', 'imok_options_page' );
		}

	//add custom settings link next to plugin deactivate link
	public static function settings_link($links){
		//$settings_link = '<a href="admin.php?page=ETSWP_settings">Settings</a>';
		$settings_link = '<a href="admin.php?page=ETSWP_settings">Settings</a>';
		array_push($links , $settings_link);
		return $links;
	}
	
	//set up admin field(s)
	public static function settings_init(  ) {
		//register_setting(string $option_group=ETSWP_admin_page , string $option_name=ETSWP_admin_settings)
		//creates an array ETSWP_admin_settings in wp_options and wp will update it according to our added setting fields
		register_setting( 'ETSWP_admin_page', 'ETSWP_admin_settings' );
		add_settings_section(
			'ETSWP_pluginPage_section',
			'Emogic Tarot Reader Settings', // section title , 
			'EmogicTarotReader_Admin::imok_settings_section_callback',
			'ETSWP_admin_page' //slug-name of the settings page on which to show the section
			//'General' //slug-name of the settings page on which to show the section
		);
		add_settings_field(
			'ETSWP_from_email_field', //Slug-name to identify the field
			'From Email', //field label
			'EmogicTarotReader_Admin::email_field_render', //callback to create field
			'ETSWP_admin_page', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);
	}
	
	public static function imok_settings_section_callback(  ) {
		//more text for Section title area
		echo 'This section description'; 
	}

	public static function email_field_render(  ) {
		$options = get_option( 'ETSWP_admin_settings' );
		$option1 = "";
		//$option1 = $options['ETSWP_from_email_field'];
		if($options != 0){
			$option1 = $options['ETSWP_from_email_field'];
		}
		//echo "<input type='text' name='imok_admin_settings[ETSWP_from_email_field]' value='{$options['imok_from_email_field']}'>";
		echo "<input type='text' name='ETSWP_from_email_field' value='{$option1}'>";
	}

	public static function options_page(  ) {
	 //ob_start();//allow return with same code
	echo"<form action='options.php' method='post'>";
	settings_fields( 'ETSWP_admin_page' );
	do_settings_sections( 'ETSWP_admin_page' );
	submit_button();
	echo"</form>";
	//return ob_get_clean(); //allow return with same code
}

}

/*
<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//disable admin bar for users
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
		}
	}

//the imok fields to be added to user profile page
//these require echo to feed to user page at correct time. return goes to a bit bucket so it does not work here
add_action( 'show_user_profile', 'imok_settings_form_echo' ); // Add the imok fields to user's own profile editing screen
add_action( 'edit_user_profile', 'imok_settings_form_echo' ); // Add the imok fields to user profile editing screen for admins
function imok_settings_form_echo( $user ){
	$html = "<h2 id='settings_top'>IMOK Data Settings Below:</h2><hr>" . imok_settings_form($user);
	echo $html;
	}

add_action( 'personal_options_update', 'imok_process_form' ); // user to process IMOK setting changes on their account page. imok_process_form() is in settings.php
add_action( 'edit_user_profile_update', 'imok_process_form' ); // admin to process user's IMOK setting.  imok_process_form() is in settings.php

//--------------------------------

//admin options.

add_action( 'admin_init', 'imok_settings_init' ); //admin_init is triggered before any other hook when a user accesses the admin area.
function imok_settings_init(  ) {
	register_setting( 'imok_admin_page', 'imok_admin_settings' ); //string $option_group, string $option_name : we are saving all settings in an array (imok_admin_settings in wp_options contains array)
	add_settings_section(
		'imok_pluginPage_section',
		__( 'imok settings', 'emogic.com' ),
		'imok_settings_section_callback',
		'imok_admin_page'
	);
	add_settings_field(
		'imok_from_email_field',
		__( 'From Email', 'emogic.com' ),
		'imok_from_email_field_render',
		'imok_admin_page',
		'imok_pluginPage_section'
	);
}

function imok_settings_section_callback(  ) {
	//echo __( 'This section description', 'emogic.com' );
}

function imok_from_email_field_render(  ) {
	$options = get_option( 'imok_admin_settings' );
	$option1 = $options['imok_from_email_field'];
	echo "<input type='text' name='imok_admin_settings[imok_from_email_field]' value='{$options['imok_from_email_field']}'>";
}

//Adds an imok link to the Dashboard Settings menu. Also creates the imok setting page 
add_action( 'admin_menu', 'imok_add_admin_menu' );
function imok_add_admin_menu(  ) {
	add_options_page( 'imok settings', 'imok', 'manage_options', 'imok_settings', 'imok_options_page' );
	//add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int $position = null ):
}

function imok_options_page(  ) {
	 //ob_start();//allow return with same code
	echo"<form action='options.php' method='post'>";
	settings_fields( 'imok_admin_page' );
	do_settings_sections( 'imok_admin_page' );
	submit_button();
	echo"</form>";
	//return ob_get_clean(); //allow return with same code
}

//add custom settings link next to plugin deactivate link
add_filter( "plugin_action_links_" . IMOK_PLUGIN_NAME , 'imok_settings_link' );
function imok_settings_link($links){
		$settings_link = '<a href="admin.php?page=imok_settings">Settings</a>';
		array_push($links , $settings_link);
		return $links;
	}

?>

*/

