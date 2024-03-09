<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//input: main form data: name, deck, spread, question : cookies for daily cards
//output: shortcodes
class EmogicTarotReader_Core {
    //run everytime a page is opened
     public static function init() {
        //to set cookies we need to do this late enough to have post data (for page and ancestor checks), but early enough before header is sent. thus template_redirect
        //template_redirect: This action hook executes just before WordPress determines which template page to load. It is a good hook to use if you need to do a redirect with full knowledge of the content that has been queried.
        add_action("template_redirect", ["EmogicTarotReader_Core", "action_shuffle_db"]); //if on a spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
        add_action("template_redirect", ["EmogicTarotReader_Core", "action_email_it", ]); //if on an email spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
       
        add_shortcode("ETSWP_get_db_item", ["EmogicTarotReader_Core", "shortcode_get_db_item"]); ////this is how we place cards on spread pages [ETSWP_get_db_item item='1' column='itemname']
        add_shortcode("ETSWP_get_cookie", ["EmogicTarotReader_Core", "shortcode_get_cookie", ]); //for reading display page [ETSWP_get_cookie name='cookie name']
        add_shortcode("ETSWP_get_input", ["EmogicTarotReader_Core", "shortcode_get_input", ]); //eg [ETSWP_get_input name='cookie name'] for reading display page. intended for just ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
        //for email, but can be used elsewhere
        add_shortcode("ETSWP_link_to_reading", ["EmogicTarotReader_Core", "shortcode_get_link_to_reading", ]); //eg [ETSWP_link_to_reading] will return a GET URL to the current reading. For use in email readings
        add_shortcode("ETSWP_reading", ["EmogicTarotReader_Core", "shortcode_get_spread_html_for_email_template"]); //eg [ETSWP_reading] will return the spread page html for the current reading. For use in the email template page.      
        add_shortcode("ETSWP_database_options", ["EmogicTarotReader_Core", "shortcode_get_deck_options", ]); //get stored options for main tarot page [ETSWP_database_options]
        add_shortcode("ETSWP_reading_options", ["EmogicTarotReader_Core", "shortcode_get_spread_options", ]); //get stored options for main page [ETSWP_reading_options]
        add_shortcode("ETSWP_pluginpath", ["EmogicTarotReader_Core", "shortcode_get_pluginpath", ]); // I use this so we can find my image folder in plugin. [ETSWP_pluginpath]

        add_filter("the_content", ["EmogicTarotReader_Core", "filter_block_html_display_on_email"], 1); //for sending email in html
        add_filter( 'wp_mail_from', ["EmogicTarotReader_Core", 'filter_email_from'] );
        add_filter( 'wp_mail_from_name', ["EmogicTarotReader_Core",'filter_email_from_name'] );
    }
     
	//this runs before wp templates are applied. We have access to data such as $post->post_parent , etc
    public static function action_shuffle_db() {
        if (!self::current_page_is_descendent_of(EMOGIC_TAROT_PLUGIN_READING_FOLDER)) { // no need to shuffle if not on a spread page
            self::build_select_options_for_form(); //build options for main form page. 
            return;
        }
        
        //get db from reading page if there is one set
        //it overrides the db set in the calling form
        //format: ETSWPdb=Leila=
        $reading_text = get_post()->post_content; //get Reading text
        //see if we have a database override in 
        $exp = "/ETSWPdb=(\w+)=/i";
        $result = preg_match( $exp , $reading_text , $matches );
        if( isset($matches[1]) && ($matches != "") ){ //override database
            $_REQUEST["ETSWP_database"] = $matches[1];
        }      
        
        //if here, we are a emogic-readings sub page
        //choose our deck
        $deck_chosen = "Emogic"; //default
        if (isset($_REQUEST["ETSWP_database"])) {
            $deck_chosen = sanitize_text_field($_REQUEST["ETSWP_database"]);
            //strip ..
            $deck_chosen = str_replace("..", "", $deck_chosen);
        }
        //read db
        $wp_post = get_page_by_path(EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER . "/" . $deck_chosen); //returns post object or null
        if (!isset($wp_post)) {
            wp_die("No Deck " . $deck_chosen, "No Deck");
        } //if no deck stop everything.
        if (ctype_space($wp_post->post_content) or $wp_post->post_content == "") {
            //already checked in options. likely an empty directory page
            return;
        }
        //get deck text and put in array
        $file_string = $wp_post->post_content;
        $file_lines = preg_split("/\r\n|\n|\r/", $file_string); //$array = preg_split ('/$\R?^/m', $string);
        $ETSWP_items_array = [];
        $ETSWP_keys_shuffled = [];
        //$ETSWP_items_array will be complete array read in order.
        //we will shuffle a separate keys array, $ETSWP_keys_shuffled
        //then we ensure that none of the key array points to another item in $ETSWP_items_array with a duplicate itemnumber
        $number_of_same_item_array = []; //so we can see if this item has another version of it in the database, and roll to see which to keep
        //1st line is the column text description
        $line_string = array_shift($file_lines);
        $field_delimeters = substr_count($line_string, EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER);
        $columns_array = explode(EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER, $line_string);
        //read all db records in order
        while (count($file_lines)) {
            $line_string = array_shift($file_lines);
            if (ctype_space($line_string)) {
                //ignore whitespace lines
                continue;
            }
            if ($line_string == "") {
                //ignore empty lines
                continue;
            }
            $count_delimeters = substr_count($line_string, EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER);
            if ($field_delimeters !== $count_delimeters) { //see if record has right number of delimiters
                wp_die("Database record has " . $count_delimeters . " record delimiters when it should have " . $field_delimeters . "</br>See: " . $line_string, "Database record error");
            }
            $line_array = explode(EMOGIC_TAROT_PLUGIN_DATABASE_DELIMITER, $line_string);
            $item_number = $line_array[0];
            $item_array = array_combine($columns_array, $line_array);
            array_push($ETSWP_items_array, $item_array);
        }
        if (isset($_REQUEST["ETSWP_keys_shuffled"])) {
            # this is likely a reading from an email link
            $json = sanitize_text_field($_REQUEST["ETSWP_keys_shuffled"]);
            $ETSWP_keys_shuffled = json_decode($json);
            //need to globalize it so we can use it in shortcode. shortcodes are called later!
            wp_cache_set(EMOGIC_TAROT_PLUGIN_DB_ARRAY_CACHE , $ETSWP_items_array); //need to globalize it so we can use it in shortcode
            wp_cache_set(EMOGIC_TAROT_PLUGIN_DB_INDEX_SHUFFLED_CACHE , $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode
            return;
        }
        //shuffled order is in cookie or we need to shuffle
        $hash = self::build_cookie_name_based_on_inputs_and_store_inputs();
        if (isset($_COOKIE[$hash])) { //simply convert cookie to cards
            $json = sanitize_text_field($_COOKIE[$hash]);
            $ETSWP_keys_shuffled = json_decode($json);
            }
        else {  //no cookies, so shuffle cards
            //create a key array and shuffle it
            $ETSWP_keys_shuffled = array_keys($ETSWP_items_array); //$ETSWP_keys_shuffled is in order at this time
            shuffle($ETSWP_keys_shuffled);
            //remove keys that point to duplicate itemnumbers in $ETSWP_items_array
            $key_exists = [];
            foreach ($ETSWP_keys_shuffled as $key) {
                $itemnumber = $ETSWP_items_array[$key]["itemnumber"];
                if (isset($key_exists[$itemnumber])) {
                    unset($ETSWP_keys_shuffled[$key]); //remove item from $ETSWP_keys_shuffled array        
                }
                $key_exists[$itemnumber] = 1;
            }
            //re-index $ETSWP_keys_shuffled as there are random holes in index as we deleted duplicate itemnumbers
            $ETSWP_keys_shuffled = array_values($ETSWP_keys_shuffled);
            
            
            
            //set shuffled deck to cookie : store shuffled db in cookie in case we re-read
            $json = json_encode($ETSWP_keys_shuffled); //save deck for specific ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
            if (isset($_REQUEST["ETSWP_database_life_in_hours"])) {
                $deck_life_in_hours = sanitize_text_field( $_REQUEST["ETSWP_database_life_in_hours"] );
            } else {
                $deck_life_in_hours = 24;
            }
            setcookie($hash, $json, time() + $deck_life_in_hours * 60 * 60, "/"); //cookie for a day
            }
     
        //need to globalize it so we can use it in shortcode. shortcodes are called later!
        wp_cache_set(EMOGIC_TAROT_PLUGIN_DB_ARRAY_CACHE , $ETSWP_items_array); //need to globalize it so we can use it in shortcode
        wp_cache_set(EMOGIC_TAROT_PLUGIN_DB_INDEX_SHUFFLED_CACHE , $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode
    }
    
      public static function action_email_it() {
        if (isset($_REQUEST["ETSWP_email_link"])) {//this is a reading from an email link. don't send another email please.
            return false;
        }
        if (!self::current_page_is_descendent_of(EMOGIC_TAROT_PLUGIN_READING_FOLDER)) {// no need to email if not on a spread page
            return;
        }
        //read email template
        $wp_post = get_page_by_path(EMOGIC_TAROT_PLUGIN_EMAIL_TEMPLATE_FOLDER . "/emogic-reading-email-template"); //returns post object or null
        if (!isset($wp_post)) {
            wp_die("No Email Template found.");
        } //if no email template stop everything.
        $email_template = do_shortcode($wp_post->post_content);    //email template will should have [ETSWP_link_to_reading] and/or [ETSWP_reading]
        if ( ! str_contains( $email_template , "just_say_yes_to_email" ) ) { //see if this reading is set for email
            return;
        }
        //validate email
		if ( isset($_REQUEST["ETSWP_email"]) ) {
			if ( ! is_email( $_REQUEST["ETSWP_email"] ) ) {
				wp_die("Email format incorrect." . sanitize_email( $_REQUEST["ETSWP_email"] ));
			}
		}
		else{//no email address
			return;
		}
        //set email to html
        add_filter("wp_mail_content_type", "EmogicTarotReader_Core::set_html_email_content_type");
        $to = sanitize_email($_REQUEST["ETSWP_email"]);
        $subject = get_option( EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION );
        $result = wp_mail( $to , $subject , $email_template);
        if($result == false){
            wp_die("Unknown email error.<p>" . sanitize_email( $_REQUEST["ETSWP_email"] ) . "</p><p>Back to site <a href='/'>Site</a></p>");
        }
        // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
        remove_filter("wp_mail_content_type", "EmogicTarotReader_Core::wpdocs_set_html_email_content_type");
    }
		        
	//for quick short code retrieval
    public static function shortcode_get_db_item($atts = [], $content = null) {
        $ETSWP_items_array = wp_cache_get( EMOGIC_TAROT_PLUGIN_DB_ARRAY_CACHE );
        $ETSWP_keys_shuffled = wp_cache_get( EMOGIC_TAROT_PLUGIN_DB_INDEX_SHUFFLED_CACHE );
        $item = $atts["item"] - 1; //the array starts at 0 so we want item 1 to point to that
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
    
    //stored in wp_cache for quick short code retrieval. Set in options wp_cache_set
    public static function shortcode_get_deck_options() {
        $page_path = EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER;
        $options = wp_cache_get("ETSWP_" . $page_path . "_options");
        return $options;
    }
    
	//stored in wp_cache for quick short code retrieval. Set in options wp_cache_set
    public static function shortcode_get_spread_options() {
        $page_path = EMOGIC_TAROT_PLUGIN_READING_FOLDER;
        $options = wp_cache_get("ETSWP_" . $page_path . "_options");
        return $options;
    }
    	
    //eg: [ETSWP_get_cookie name='ETSWP_database']
    public static function shortcode_get_cookie($atts = [], $content = null) {
        $name = $atts["name"];
        isset($_COOKIE[$name]) ? ($cookie = sanitize_text_field($_COOKIE[$name])) : ($cookie = "");
        return $cookie;
    }
    
    //eg: [ETSWP_get_db_item item='3' column='itemimage']
    public static function shortcode_get_input($atts = [], $content = null) {
        $name = $atts["name"];
        isset($_REQUEST[$name]) ? ($input = sanitize_text_field($_REQUEST[$name])) : ($input = "");
        return $input;
    }
    
	public static function shortcode_get_link_to_reading() {
        //usually for email template
        $post_url = get_post()->guid;
        $actual_link = $post_url;
        $actual_link.= "?";
        $actual_link.= isset($_REQUEST["ETSWP_first_name"]) ? "&" . "ETSWP_first_name=" . sanitize_text_field($_REQUEST["ETSWP_first_name"]) : "";
        $actual_link.= isset($_REQUEST["ETSWP_database"]) ? "&" . "ETSWP_database=" . sanitize_text_field($_REQUEST["ETSWP_database"]) : "";
        $actual_link.= isset($_REQUEST["ETSWP_question"]) ? "&" . "ETSWP_question=" . sanitize_text_field($_REQUEST["ETSWP_question"]) : "";
        $actual_link.= "&" . "ETSWP_email_link=1";
        $ETSWP_keys_shuffled = wp_cache_get( EMOGIC_TAROT_PLUGIN_DB_INDEX_SHUFFLED_CACHE );
        $json = json_encode($ETSWP_keys_shuffled);
        $actual_link.= isset($json) ? "&" . "ETSWP_keys_shuffled=" . sanitize_text_field($json) : "";
        return $actual_link;
    }
    
	public static function shortcode_get_spread_html_for_email_template() { //return the whole spread into the email template using [ETSWP_reading]
        $post = get_post();
        $html = do_shortcode($post->post_content);
        return $html;
    }
	
    //Filter the mail content type.
    public static function set_html_email_content_type() {
        return "text/html";
    }
    
    // Function to change email address
    public static function filter_email_from( $original_email_address ) {
        require_once EMOGIC_TAROT_PLUGIN_PATH . 'admin/EmogicTarotReader_Admin.php';
         $option = get_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION );
        if(isset($option) == false || $option == ""){
            return $original_email_address;
        }
        return $option;
    }
 
    // Function to change sender name
    public static function filter_email_from_name( $original_email_from ) {
        require_once EMOGIC_TAROT_PLUGIN_PATH . 'admin/EmogicTarotReader_Admin.php';
        $option = get_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION );
        if(isset($option) == false || $option == ""){
            return $original_email_from;
        }
        return $option;
    }

    public static function filter_block_html_display_on_email($content) {
        // Check if we're inside the main loop in a single Post.
        if (is_singular() && in_the_loop() && is_main_query()) {
            //just_say_no_to_display
            if (isset($_REQUEST["ETSWP_email_link"])) {
                // allow readings from email link
                return $content;
            }
            if (str_contains($content, "redirect_to_email_has_been_sent")) {
                //see if we want to redirect output
                $wp_post = get_page_by_path(EMOGIC_TAROT_PLUGIN_EMAIL_TEMPLATE_FOLDER . "/emogic-email-has-been-sent"); //returns post object or null
                if (!isset($wp_post)) {
                    wp_die("No Email Has Been Sent Template found.");
                } //if no email template stop everything.
                //do shortcode just in case, eg for username etc
                $content = do_shortcode($wp_post->post_content);
            }
        } 
        return $content;
    }
    
    //action_shuffle_db() calls this
    //build both emogic-database and emogic-readings options from wp pages
    public static function build_select_options_for_form() {
        $page_paths = [EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER, EMOGIC_TAROT_PLUGIN_READING_FOLDER, ];
        foreach ($page_paths as $page_path) {
            $html = "";
            $wp_post = get_page_by_path($page_path); //returns post object or null
            if (!isset($wp_post)) {
                wp_die("Missing Reading or Database page" . "</p><p>Back to site <a href='/'>Site</a></p>");
            } //no $page_path stop everything
            $parent_id = $wp_post->ID;
            $page_path_parent = $page_path;
            $html = self::build_selected_options_recursive($parent_id, $page_path); //result will be the options text. then we can get rid off the fancy shortcode and simplify. no longer need $files
            wp_cache_set("ETSWP_" . $page_path . "_options", $html);
        }
    }
    
	//build_select_options_for_form() calls this. navigates sub folders and files
    public static function build_selected_options_recursive($parent_id, $page_path_parent) {
        //note: recursive routine, do not change arg values here!!!
        $html = "";
        $html_children = "";
        $children = get_children($parent_id);
        if (!isset($children)) {
            return $html;
        } //no branch $html = ''
        foreach ($children as $child) {
            if ($page_path_parent == "") {
                //root page
                $path = $child->post_title;
            }
            //not root, a sub page
            else {
                $path = $page_path_parent . "/" . $child->post_title;
            }
            if (!(ctype_space($child->post_content) or $child->post_content == "")) {
                //empty, likely a directory page
                if (0 === strpos($path, EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER)) {
                    $path_tmp = preg_replace("/^" . EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER . "\//", "", $path);
                    $html = $html . "<option value='$path_tmp'>$path_tmp</option>";
                }
                if (0 === strpos($path, EMOGIC_TAROT_PLUGIN_READING_FOLDER)) {
                    $path_tmp = preg_replace("/^" . EMOGIC_TAROT_PLUGIN_READING_FOLDER . "\//", "", $path);
                    $perma = get_permalink($child->ID, false);
                    $html = $html . "<option value='" . $perma . "'>$path_tmp</option>";
                }
            }
            $html_children = self::build_selected_options_recursive($child->ID, $path);
            $html = $html . $html_children;
        }
        return $html;
    }
    
    //returns a  hash of form inputs of ['ETSWP_first_name' , 'ETSWP_database' , 'ETSWP_reading' , 'ETSWP_question']. this allows different readings for different names, questions, spreads
    //but also sets cookies for ['ETSWP_first_name' , 'ETSWP_database' , 'ETSWP_reading' , 'ETSWP_question'] and ETSWP_email
    public static function build_cookie_name_based_on_inputs_and_store_inputs() {
        $cookie_name = "";
        $cookie_array = ["ETSWP_first_name", "ETSWP_database", "ETSWP_reading", "ETSWP_question",];
        foreach ($cookie_array as $cookie) {
            if (isset($_REQUEST[$cookie])) {
                $cookie_name = $cookie_name . sanitize_text_field($_REQUEST[$cookie]); //build cookie name for card reading
                $result = setcookie($cookie, sanitize_text_field($_REQUEST[$cookie]), time() + 365 * 24 * 60 * 60, "/"); //save cookie of form fields from main tarot page    
            }
        }
		if ( isset($_REQUEST["ETSWP_email"]) ){ 
			if ( is_email($_REQUEST["ETSWP_email"]) || ctype_space( $_REQUEST["ETSWP_email"] ) || ($_REQUEST["ETSWP_email"] == '') ){ //also check for blank email which is valid! 
				$result = setcookie("ETSWP_email", sanitize_text_field($_REQUEST["ETSWP_email"]), time() + 365 * 24 * 60 * 60, "/"); //save cookie for ETSWP_email
			}
		}
        $hash = hash("crc32", $cookie_name); //convert cookie name to hash
        return $hash;
    }

	public static function current_page_is_descendent_of($path) {
        $id = get_queried_object_id();
        $ancestors = get_ancestors($id, "page"); //get array of ancestor pages of current page
        if (count($ancestors) == 0) {
            return 0;
        } //no ancestors
        $page_id = get_page_by_path($path)->ID;
        if (in_array($page_id, $ancestors)) {
            return 1;
        } else {
            return 0;
        }
    }
    	
}