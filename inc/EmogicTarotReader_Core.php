<?php
//input: main form data: name, deck, spread, question : cookies for daily cards
//output: shortcodes
class EmogicTarotReader_Core {
    //run everytime a page is opened
    function run() {
        //both actions set cookies, so we need to do this late enough to have post data (for page and ancestor checks), but early enough before header is sent. thus template_redirect
        //template_redirect: Fires before determining which template to load.
        //template_redirect: This action hook executes just before WordPress determines which template page to load. It is a good hook to use if you need to do a redirect with full knowledge of the content that has been queried.
        //template_redirect: best time to get form data
        add_action("template_redirect", ["EmogicTarotReader_Core", "shuffle"]); //if on a spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
        //add_action( 'template_redirect', array('EmogicTarotReader_Core','set_shuffled_db_in_cookie') ); //set our shuffled card cookies
        //add_action("wp_print_footer_scripts", ["EmogicTarotReader_Core", "email_it", ]); //if on an email spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
        add_action("template_redirect", ["EmogicTarotReader_Core", "email_it", ]); //if on an email spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
        add_shortcode("ETSWP_deck_options", ["EmogicTarotReader_Core", "deck_options", ]); //get stored options for main tarot page [ETSWP_deck_options]
        add_shortcode("ETSWP_spread_options", ["EmogicTarotReader_Core", "spread_options", ]); //get stored options for main page [ETSWP_spread_options]
        add_shortcode("ETSWP_get_item", ["EmogicTarotReader_Core", "get_item"]); ////this is how we place cards on spread pages [ETSWP_get_item item='1' column='itemname']
        add_shortcode("ETSWP_pluginpath", ["EmogicTarotReader_Core", "get_pluginpath", ]); // I use this so we can find my image folder in plugin. [ETSWP_pluginpath]
        add_shortcode("ETSWP_get_cookie", ["EmogicTarotReader_Core", "get_cookie", ]); //for reading display page [ETSWP_get_cookie name='cookie name']
        add_shortcode("ETSWP_get_input", ["EmogicTarotReader_Core", "get_input", ]); //eg [ETSWP_get_input name='cookie name'] for reading display page. intended for just ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
        //for email, but can be used elsewhere
        add_shortcode("ETSWP_link_to_reading", ["EmogicTarotReader_Core", "get_link_to_reading", ]); //eg [ETSWP_link_to_reading] will return a GET URL to the current reading. For use in email readings
        add_shortcode("ETSWP_spread", ["EmogicTarotReader_Core", "get_spread"]); //eg [ETSWP_spread] will return the spread page for the current reading. For use in the email template page.
        add_filter("the_content", ["EmogicTarotReader_Core", "filter_block_html_display_on_email"], 1); //for sending email in html
        // Hooking up our functions to WordPress filters 
        add_filter( 'wp_mail_from', ["EmogicTarotReader_Core", 'mail_from'] );
        add_filter( 'wp_mail_from_name', ["EmogicTarotReader_Core",'mail_from_name'] );
    }
    
	//this runs before wp templates are applied. We have access to data such as $post->post_parent , etc
    public static function shuffle() {
        self::options(); //build options for main form page. Always run it for all pages KISS
        if (!self::is_descendent_page_of(EMOGIC_TAROT_PLUGIN_READING_FOLDER)) {
            // no need to shuffle if not on a spread page
            return;
        }
        //if here, we are a emogic-readings sub page
        //choose our deck
        $deck_chosen = "Emogic"; //default
        if (isset($_REQUEST["ETSWP_deck"])) {
            $deck_chosen = sanitize_text_field($_REQUEST["ETSWP_deck"]);
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
            if ($field_delimeters !== $count_delimeters) {
                //see if record has right number of delimiters
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
            wp_cache_set("ETSWP_items_array", $ETSWP_items_array); //need to globalize it so we can use it in shortcode
            wp_cache_set("ETSWP_keys_shuffled", $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode
            return;
        }
        //shuffled order is in cookie or we need to shuffle
        $hash = self::build_cookie_name();
        if (isset($_COOKIE[$hash])) {
            //simply convert cookie to cards
            //if( isset($_REQUEST[$hash]) ){//simply convert cookie to cards
            $json = sanitize_text_field($_COOKIE[$hash]);
            //$json = sanitize_text_field( $_REQUEST[$hash] );
            $ETSWP_keys_shuffled = json_decode($json);
        } else {
            //no cookies, shuffle cards
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
            //re-index $ETSWP_keys_shuffled as there are random holes in index
            $ETSWP_keys_shuffled = array_values($ETSWP_keys_shuffled);
        }
        //store shuffled db in cookie in case we re-read
        $hash = self::build_cookie_name();
        if (!isset($_COOKIE[$hash])) {
            $json = json_encode($ETSWP_keys_shuffled); //save deck for specific ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
            if (isset($_REQUEST["ETSWP_deck_life_in_hours"])) {
                $deck_life_in_hours = $_REQUEST["ETSWP_deck_life_in_hours"];
            } else {
                $deck_life_in_hours = 24;
            }
            setcookie($hash, $json, time() + $deck_life_in_hours * 60 * 60, "/"); //cookie for a day
            
        }
        //need to globalize it so we can use it in shortcode. shortcodes are called later!
        wp_cache_set("ETSWP_items_array", $ETSWP_items_array); //need to globalize it so we can use it in shortcode
        wp_cache_set("ETSWP_keys_shuffled", $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode
        
    }
    
	//shuffle() calls this
    //build both emogic-database and emogic-readings options from wp pages
    public static function options() {
        $page_paths = [EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER, EMOGIC_TAROT_PLUGIN_READING_FOLDER, ];
        foreach ($page_paths as $page_path) {
            $html = "";
            $wp_post = get_page_by_path($page_path); //returns post object or null
            if (!isset($wp_post)) {
                return;
            } //no $page_path stop everything
            $parent_id = $wp_post->ID;
            $page_path_parent = $page_path;
            $html = self::options_recursive_pages($parent_id, $page_path); //result will be the options text. then we can get rid off the fancy shortcode and simplify. no longer need $files
            wp_cache_set("ETSWP_" . $page_path . "_options", $html);
        }
    }
    
	//options() calls this. navigates sub folders and files
    public static function options_recursive_pages($parent_id, $page_path_parent) {
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
            $html_children = self::options_recursive_pages($child->ID, $path);
            $html = $html . $html_children;
        }
        return $html;
    }
    
	//stored in wp_cache for quick short code retrieval. Set in options wp_cache_set
    public static function deck_options() {
        $page_path = EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER;
        $options = wp_cache_get("ETSWP_" . $page_path . "_options");
        return $options;
    }
    
	//stored in wp_cache for quick short code retrieval. Set in options wp_cache_set
    public static function spread_options() {
        $page_path = EMOGIC_TAROT_PLUGIN_READING_FOLDER;
        $options = wp_cache_get("ETSWP_" . $page_path . "_options");
        return $options;
    }
    
	public static function is_descendent_page_of($path) {
        //will only work when post is available. eg, after the_post hook
        $id = get_queried_object_id();
        $ancs = get_ancestors($id, "page"); //get array of ancestor pages of current page
        if (count($ancs) == 0) {
            return 0;
        } //no ancestors
        $page_id = get_page_by_path($path)->ID;
        //BUG $post will not work here!!!!
        //if ( in_array($page_id , $ancs) || $post->post_parent == "$page_id" )
        if (in_array($page_id, $ancs)) {
            return 1;
        } else {
            return 0;
        }
    }
	
	//called every 
    public static function email_it() {
        if (isset($_REQUEST["ETSWP_email_link"])) {//this is a reading from an email link. don't send another email please.
            return false;
        }
        if (!self::is_descendent_page_of(EMOGIC_TAROT_PLUGIN_READING_FOLDER)) {// no need to email if not on a spread page
            return;
        }
        //read email template
        $wp_post = get_page_by_path(EMOGIC_TAROT_PLUGIN_EMAIL_TEMPLATE_FOLDER . "/emogic-reading-email-template"); //returns post object or null
        if (!isset($wp_post)) {
            wp_die("No Email Template found.");
        } //if no email template stop everything.
		$email_template = do_shortcode($wp_post->post_content);
        //email template will should have [ETSWP_link_to_reading] and/or [ETSWP_spread]
        if (!str_contains($email_template, "just_say_yes_to_email")) {
            //see if this reading is set for email
            return;
        }
		//validate email
		if ( isset($_REQUEST["ETSWP_email"]) ) {
			if ( ! is_email($_REQUEST["ETSWP_email"]) ) {
				wp_die("Email format incorrect." . $_REQUEST["ETSWP_email"]);
			}
		}
		else{//no email address
			return;
		}
        //set email to html
        add_filter("wp_mail_content_type", "EmogicTarotReader_Core::set_html_mail_content_type");
        //$html = do_shortcode($email_template); not required
        $result = wp_mail( sanitize_email($_REQUEST["ETSWP_email"]) , "test", $email_template);
        // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
        remove_filter("wp_mail_content_type", "EmogicTarotReader_Core::wpdocs_set_html_mail_content_type");
    }
	
    //Filter the mail content type.
    public static function set_html_mail_content_type() {
        return "text/html";
    }
    
    // Function to change email address
    public static function mail_from( $original_email_address ) {
         $option = get_option( 'ETSWP_from_email_field' );
         //$email_address = "tarot@tarot.emogic.com";
        if(isset($option) == false || $option == ""){
            return $original_email_address;
        }
        return $option;
    }
 
    // Function to change sender name
    public static function mail_from_name( $original_email_from ) {
         $option = get_option( 'ETSWP_email_display_name_field' );
        if(isset($option) == false || $option == ""){
            return $original_email_from;
        }
        return $option;
    }
     
	//for quick short code retrieval
    public static function get_item($atts = [], $content = null) {
        $ETSWP_items_array = wp_cache_get("ETSWP_items_array");
        $ETSWP_keys_shuffled = wp_cache_get("ETSWP_keys_shuffled");
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
    public static function get_pluginpath($atts = [], $content = null) {
        return EMOGIC_TAROT_PLUGIN_LOCATION_URL;
    }
    
	//returns a  hash of form inputs of ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question']. this allows different readings for different names, questions, spreads
    //but also sets cookies for ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'] and ETSWP_email
    public static function build_cookie_name() {
        $cookie_name = "";
        $cookie_array = ["ETSWP_first_name", "ETSWP_deck", "ETSWP_spread", "ETSWP_question", ];
        foreach ($cookie_array as $cookie) {
            if (isset($_REQUEST[$cookie])) {
                $cookie_name = $cookie_name . sanitize_text_field($_REQUEST[$cookie]); //build cookie name for card reading
                $result = setcookie($cookie, sanitize_text_field($_REQUEST[$cookie]), time() + 365 * 24 * 60 * 60, "/"); //save cookie of form fields from main tarot page    
            }
        }
		if ( isset($_REQUEST["ETSWP_email"]) ){
			if ( is_email($_REQUEST["ETSWP_email"]) ){
				$result = setcookie("ETSWP_email", sanitize_text_field($_REQUEST["ETSWP_email"]), time() + 365 * 24 * 60 * 60, "/"); //save cookie for ETSWP_email
			}
		}
        $hash = hash("crc32", $cookie_name); //convert cookie name to hash
        return $hash;
    }
	
    //for shortcode
    //[ETSWP_get_cookie name='ETSWP_deck']
    public static function get_cookie($atts = [], $content = null) {
        $name = $atts["name"];
        isset($_COOKIE[$name]) ? ($cookie = sanitize_text_field($_COOKIE[$name])) : ($cookie = "");
        return $cookie;
    }
    
	//for shortcode
    //[ETSWP_get_item item='3' column='itemimage']
    public static function get_input($atts = [], $content = null) {
        $name = $atts["name"];
        isset($_REQUEST[$name]) ? ($input = sanitize_text_field($_REQUEST[$name])) : ($input = "");
        return $input;
    }
    
	public static function get_link_to_reading() {
        //usually for email template
        $post_url = get_post()->guid;
        $actual_link = $post_url;
        //$actual_link = isset($_REQUEST["ETSWP_spread"]) ? sanitize_text_field( $_REQUEST["ETSWP_spread"] ) : "";
        $actual_link.= "?";
        $actual_link.= isset($_REQUEST["ETSWP_first_name"]) ? "&" . "ETSWP_first_name=" . sanitize_text_field($_REQUEST["ETSWP_first_name"]) : "";
        $actual_link.= isset($_REQUEST["ETSWP_deck"]) ? "&" . "ETSWP_deck=" . sanitize_text_field($_REQUEST["ETSWP_deck"]) : "";
        $actual_link.= isset($_REQUEST["ETSWP_question"]) ? "&" . "ETSWP_question=" . sanitize_text_field($_REQUEST["ETSWP_question"]) : "";
        $actual_link.= "&" . "ETSWP_email_link=1";
        $ETSWP_keys_shuffled = wp_cache_get("ETSWP_keys_shuffled");
        $json = json_encode($ETSWP_keys_shuffled);
        $actual_link.= isset($json) ? "&" . "ETSWP_keys_shuffled=" . sanitize_text_field($json) : "";
        return $actual_link;
    }
    
	public static function get_spread() {
        //usually for email template
        $post = get_post();
        $html = do_shortcode($post->post_content);
        return $html;
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
            //return $content . esc_html__( 'I’m filtering the content inside the main loop', 'wporg');
            
        }
        return $content;
    }
	
	/* Import media from url
 *
 * @param string $file_url URL of the existing file from the original site
 * @param int $post_id The post ID of the post to which the imported media is to be attached
 *
 * @return boolean True on success, false on failure
 */

function fetch_media($file_url, $post_id) {
	require_once(ABSPATH . 'wp-load.php');
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	global $wpdb;

	if(!$post_id) {
		return false;
	}

	//directory to import to	
	$artDir = 'wp-content/uploads/importedmedia/';

	//if the directory doesn't exist, create it	
	if(!file_exists(ABSPATH.$artDir)) {
		mkdir(ABSPATH.$artDir);
	}

	//rename the file... alternatively, you could explode on "/" and keep the original file name
	$ext = array_pop(explode(".", $file_url));
	$new_filename = 'blogmedia-'.$post_id.".".$ext; //if your post has multiple files, you may need to add a random number to the file name to prevent overwrites

	if (@fclose(@fopen($file_url, "r"))) { //make sure the file actually exists
		copy($file_url, ABSPATH.$artDir.$new_filename);

		$siteurl = get_option('siteurl');
		$file_info = getimagesize(ABSPATH.$artDir.$new_filename);

		//create an array of attachment data to insert into wp_posts table
		$artdata = array();
		$artdata = array(
			'post_author' => 1, 
			'post_date' => current_time('mysql'),
			'post_date_gmt' => current_time('mysql'),
			'post_title' => $new_filename, 
			'post_status' => 'inherit',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $new_filename)),											'post_modified' => current_time('mysql'),
			'post_modified_gmt' => current_time('mysql'),
			'post_parent' => $post_id,
			'post_type' => 'attachment',
			'guid' => $siteurl.'/'.$artDir.$new_filename,
			'post_mime_type' => $file_info['mime'],
			'post_excerpt' => '',
			'post_content' => ''
		);

		$uploads = wp_upload_dir();
		$save_path = $uploads['basedir'].'/importedmedia/'.$new_filename;

		//insert the database record
		$attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );

		//generate metadata and thumbnails
		if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
			wp_update_attachment_metadata($attach_id, $attach_data);
		}

		//optional make it the featured image of the post it's attached to
		$rows_affected = $wpdb->insert($wpdb->prefix.'postmeta', array('post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id));
	}
	else {
		return false;
	}
	return true;
}
	
/* 
* This function copy $source directory and all files 
* and sub directories to $destination folder
*/

	function recursive_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recursive_copy($src .'/'. $file, $dst .'/'. $file);
				}
				else {
					copy($src .'/'. $file,$dst .'/'. $file);
				}
			}
		}
		closedir($dir);
	}
	
}
