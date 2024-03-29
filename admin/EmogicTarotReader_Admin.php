<?php
 
class EmogicTarotReader_Admin {

	const ADMIN_SLUG = "ETSWP_settings";
	const HELP_SLUG = "emogic-tarot-help";
	
	public static function init() {
		//set up wp section and fields for our admin settings page
		add_action( 'admin_init', 'EmogicTarotReader_Admin::register_settings_and_fields_for_admin_page' ); //admin_init is triggered before any other hook when a user accesses the admin area.
		//create admin settings page and a slug to it
		add_action( 'admin_menu', 'EmogicTarotReader_Admin::create_admin_page_and_slug' );
		//set up 'settings' link in plugin menu
		add_filter( "plugin_action_links_" . EMOGIC_TAROT_RELATIVE_PLUGIN_PATH , 'EmogicTarotReader_Admin::create_plugin_setting_link' ); 
	}
	
		public static function create_admin_page_and_slug() { //create menu page and a slug to it
		//add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int $position = null ):
		//add_options_page( 'Emogic Tarot Reader Settings', 'Emogic Tarot Reader Settings', 'manage_options', self::ADMIN_SLUG , 'EmogicTarotReader_Admin::build_form_options_page' );
		
		// Add the top-level admin menu
		$page_title = 'Emogic Tarot Reader';
		$menu_title = 'Emogic Tarot Reader';
		$capability = 'manage_options';
		$menu_slug = self::ADMIN_SLUG;
		$function = 'EmogicTarotReader_Admin::build_form_options_page';
		add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
		// Add submenu page with same slug as parent to ensure no duplicates
		$sub_menu_title = 'Settings';
		add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
		// Now add the submenu page for Help
		$submenu_page_title = 'Emogic Tarot Reader';
		$submenu_title = 'Help';
		$submenu_slug = self::HELP_SLUG;
		$submenu_function = '';
		add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);

		}
		
	public static function build_form_options_page(  ) {
	   echo"<form action='options.php' method='post'>";
	   //settings_fields( string $option_group ) Outputs nonce, action, and option_page fields for a settings page.
	   settings_fields( 'ETSWP_option_group' );
	   //do_settings_sections( string $page ) Prints out all settings sections added to a particular settings page.
	   do_settings_sections( 'ETSWP_option_group' );
	   //submit_button( string $text = null, string $type = ‘primary’, string $name = ‘submit’, bool $wrap = true, array|string $other_attributes = null )
	   submit_button(); //Echoes a submit button, with provided text and appropriate class(es).
	   echo"</form>";
   }

	//set up 'settings' link in plugin menu
	public static function create_plugin_setting_link($links){
		$settings_link = '<a href="admin.php?page=' . self::ADMIN_SLUG . '">Settings</a>&nbsp;|&nbsp;<a href="' . EMOGIC_TAROT_PLUGIN_WP_ROOT_URL . '/' . self::HELP_SLUG . '">Help</a>    ';
		array_push($links , $settings_link);
		return $links;
	}
	
	//set up wp section and fields for our admin settings page
	public static function register_settings_and_fields_for_admin_page(  ) {
		//register_setting(string $option_group, string $option_name) Registers a setting and its data.
		//creates an array ETSWP_admin_settings in wp_options and wp will update it according to our added setting fields
		register_setting( 'ETSWP_option_group', EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION );
		register_setting( 'ETSWP_option_group', EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION );
		register_setting( 'ETSWP_option_group', EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION );

		//add_settings_section( string $id, string $title, callable $callback, string $page, array $args = array() )
 		add_settings_section(
			'ETSWP_pluginPage_section',
			'Emogic Tarot Reader Settings', // section title , 
			'EmogicTarotReader_Admin::imok_settings_section_callback',
			'ETSWP_option_group' //slug-name of the settings page on which to show the section
		);
		//add_settings_field( string $id, string $title, callable $callback, string $page, string $section = ‘default’, array $args = array() )
		add_settings_field(
			EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION, //Slug-name to identify the field
			'Email Display Name', //field label
			'EmogicTarotReader_Admin::email_display_name_field_render', //callback to create field
			'ETSWP_option_group', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);
		add_settings_field(
			EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION, //Slug-name to identify the field
			'From Email', //field label
			'EmogicTarotReader_Admin::email_field_render', //callback to create field
			'ETSWP_option_group', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);		
		add_settings_field(
			EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION, //Slug-name to identify the field
			'Subject', //field label
			'EmogicTarotReader_Admin::email_subject_field_render', //callback to create field
			'ETSWP_option_group', //slug-name of the settings page on which to show the section
			'ETSWP_pluginPage_section'
		);		
	}
	
	public static function imok_settings_section_callback(  ) {
		//more text for Section title area
		echo 'These settings affect the email from address. If  left blank, Wordpress defaults will be set.'; 
	}

	public static function email_subject_field_render(  ) {
		$option = sanitize_text_field( get_option( EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION ) );
		echo "<input type='text' name='" . EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION . "' value='" . esc_attr( $option ) . "' placeholder='eg: Tarot Reading'>";
	}
	public static function email_field_render(  ) {
		$option = sanitize_text_field( get_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION ) );
		echo "<input type='email' name='" . EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION . "' value=' " . esc_attr( $option ) . "' placeholder='eg: tarot@yourdomain.com'>";
	}

	public static function email_display_name_field_render(  ) {
		$option = sanitize_text_field( get_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION ) );
		echo "<input type='text' name='" . EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION . "' value='" . esc_attr( $option ) . "' placeholder='eg: Tarot Mailer'>";
	}

}