<?php

class EmogicTarotReader_Admin {

	public static function run() {
		//set up wp section and fields for our admin settings page
		add_action( 'admin_init', 'EmogicTarotReader_Admin::register_settings_and_fields_for_admin_page' ); //admin_init is triggered before any other hook when a user accesses the admin area.
		//create admin settings page and a slug to it
		add_action( 'admin_menu', 'EmogicTarotReader_Admin::create_admin_page_and_slug' );
		//set up 'settings' link in plugin menu
		add_filter( "plugin_action_links_" . EMOGIC_TAROT_PLUGIN_NAME , 'EmogicTarotReader_Admin::create_plugin_setting_link' ); 
	}
	
		public static function create_admin_page_and_slug() { //create menu page and a slug to it
		//add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int $position = null ):
		add_options_page( 'Emogic Tarot Reader Settings', 'Emogic Tarot Reader Settings', 'manage_options', 'ETSWP_settings', 'EmogicTarotReader_Admin::build_form_options_page' );	
		}
		
	public static function build_form_options_page(  ) {
		//ob_start();//allow return with same code
	   echo"<form action='options.php' method='post'>";
	   //settings_fields( string $option_group ) Outputs nonce, action, and option_page fields for a settings page.
	   settings_fields( 'ETSWP_option_group' );
	   //do_settings_sections( string $page ) Prints out all settings sections added to a particular settings page.
	   do_settings_sections( 'ETSWP_option_group' );
	   //submit_button( string $text = null, string $type = ‘primary’, string $name = ‘submit’, bool $wrap = true, array|string $other_attributes = null )
		//Echoes a submit button, with provided text and appropriate class(es).
	   submit_button();
	   echo"</form>";
	   //return ob_get_clean(); //allow return with same code
   }

	//set up 'settings' link in plugin menu
	public static function create_plugin_setting_link($links){
		//$settings_link = '<a href="admin.php?page=ETSWP_settings">Settings</a>';
		$settings_link = '<a href="admin.php?page=ETSWP_settings">Settings</a>';
		array_push($links , $settings_link);
		return $links;
	}
	
	//set up wp section and fields for our admin settings page
	public static function register_settings_and_fields_for_admin_page(  ) {
		//register_setting(string $option_group, string $option_name) Registers a setting and its data.
		//creates an array ETSWP_admin_settings in wp_options and wp will update it according to our added setting fields
		//register_setting( 'ETSWP_admin_page', 'ETSWP_admin_settings' );
		register_setting( 'ETSWP_option_group', 'ETSWP_options' );
		//add_settings_section( string $id, string $title, callable $callback, string $page, array $args = array() )
		add_settings_section(
			'ETSWP_pluginPage_section',
			'Emogic Tarot Reader Settings', // section title , 
			'EmogicTarotReader_Admin::imok_settings_section_callback',
			'ETSWP_option_group' //slug-name of the settings page on which to show the section
		);
		//add_settings_field( string $id, string $title, callable $callback, string $page, string $section = ‘default’, array $args = array() )
		add_settings_field(
			'ETSWP_from_email_field', //Slug-name to identify the field
			'From Email', //field label
			'EmogicTarotReader_Admin::email_field_render', //callback to create field
			'ETSWP_option_group', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);
		add_settings_field(
			'ETSWP_email_display_name_field', //Slug-name to identify the field
			' Email Display Name', //field label
			'EmogicTarotReader_Admin::email_display_name_field_render', //callback to create field
			'ETSWP_option_group', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);
	
		//also set default options if none already
		$options = get_option( 'ETSWP_options' );
		if($options == false || $options == ""){
			$options = array();
			$ETSWP_email_display_name_field = "Tarot Mailer";
			$ETSWP_from_email_field = "tarot@emogic.com";
			$options["ETSWP_email_display_name_field"] = $ETSWP_email_display_name_field;
			$options["ETSWP_from_email_field"] = $ETSWP_from_email_field;
			update_option( 'ETSWP_options' ,  $options );
		}
			
	}
	
	public static function imok_settings_section_callback(  ) {
		//more text for Section title area
		echo 'These settings affect the email from address'; 
	}

	public static function email_field_render(  ) {
		$options = get_option( 'ETSWP_options' );
		$ETSWP_from_email_field = $options["ETSWP_from_email_field"];
		//name must be the option_name[option_field]
		echo "<input type='text' name='ETSWP_options[ETSWP_from_email_field]' value='{$ETSWP_from_email_field}'>";
	}

	public static function email_display_name_field_render(  ) {
		$options = get_option( 'ETSWP_options' );
		$ETSWP_email_display_name_field = $options["ETSWP_email_display_name_field"];
		//name must be the option_name[option_field]
		echo "<input type='text' name='ETSWP_options[ETSWP_email_display_name_field]' value='{$ETSWP_email_display_name_field}'>";
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

