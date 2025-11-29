<?php
 
 add_action( 'edit_form_after_title', 'my_new_elem_after_title' );
function my_new_elem_after_title() {
    return '<h2>Your new element after title</h2>';
}

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

		// NEW card backs on admin
		//add_shortcode("ETSWP_get_db_item", ["EmogicTarotReader_Admin", "shortcode_get_db_item"]); ////this is how we place cards on spread pages [ETSWP_get_db_item item='1' column='itemname']
		add_shortcode("ETSWP_pluginpath", ["EmogicTarotReader_Admin", "shortcode_get_pluginpath", ]); // I use this so we can find my image folder in plugin. [ETSWP_pluginpath]
	
		add_action( 'admin_post_nopriv', ["EmogicTarotReader_Admin",'wporg_filter_title'] );
	//the_editor_content edit_form_after_editor wp_loaded the_post : wp hook
	}

	function wporg_filter_title( $post ) {
		echo 'bbop' . $post. var_dump($post);
	}
	

		// NEW card backs on admin
		//for quick short code retrieval
		public static function shortcode_get_db_item($atts = [], $content = null) {
			//recover previously stored shuffle data and deck db from wp_cache_get
			//$ETSWP_items_array = wp_cache_get( EMOGIC_TAROT_PLUGIN_DB_ARRAY_CACHE );
			//$ETSWP_keys_shuffled = wp_cache_get( EMOGIC_TAROT_PLUGIN_DB_INDEX_SHUFFLED_CACHE );
			$item = $atts["item"] - 1; //the array starts at 0 so we want item 1, in shortcode, to point to 0 in the db
			$column = $atts["column"];
			$output = $ETSWP_items_array[$ETSWP_keys_shuffled[$item]][$column];
			//first_name replace
			$first_name = "Seeker";
			if (isset($_REQUEST["ETSWP_first_name"]) and $_REQUEST["ETSWP_first_name"] != "") {
				$first_name = sanitize_text_field($_REQUEST["ETSWP_first_name"]);
			}
			$output = str_replace("[first_name]", $first_name, $output);
			return $output;
		}

			//for quick short code retrieval
			public static function shortcode_get_pluginpath($atts = [], $content = null) {
				return EMOGIC_TAROT_PLUGIN_LOCATION_URL;
			}

	//Your best bet is to create a custom page template using PHP for the specific page and
	// then use <?php echo do_shortcode( '[grid class="center-xs]Hello World[/grid]' ); inside there.
		
	
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