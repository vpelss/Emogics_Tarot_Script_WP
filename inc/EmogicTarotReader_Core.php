<?php
//ip: path to deck or deck data : cookies for daily cards
//op: shortcodes

class EmogicTarotReader_Core {

	function run(){
		//if( is_admin() ){ return; }  // no need for any of this on an admin
		//both actions set cookies, so we need to do this late enough to have post data (for page and ancestor checks), but early enough before header is sent. thus template_redirect
		//   template_redirect posts_results
		add_action( 'template_redirect', array('EmogicTarotReader_Core','shuffle') ); //if on a spread page, get deck and spread from form, shuffle cards, set up shortcodes, set cookies based on calling form fields.
		add_action( 'template_redirect', array('EmogicTarotReader_Core','set_tarot_shuffled_cards_cookie') ); //set our shuffled card cookies

		add_shortcode( 'ETSWP_deck_options' , array( 'EmogicTarotReader_Core' ,'deck_options') ); //get stored options for main tarot page [ETSWP_deck_options]
		add_shortcode( 'ETSWP_spread_options' , array('EmogicTarotReader_Core','spread_options') ); //get stored options for main page [ETSWP_spread_options]
		add_shortcode( 'ETSWP_get_item' , array('EmogicTarotReader_Core','get_item') ); ////this is how we place cards on spread pages [ETSWP_get_item item='1' column='itemname']
		add_shortcode( 'ETSWP_pluginpath' , array('EmogicTarotReader_Core','get_pluginpath') ); // I use this so we can find my image folder in plugin. [ETSWP_pluginpath]
		add_shortcode( 'ETSWP_get_cookie' , array('EmogicTarotReader_Core','get_cookie') ); //for reading display page [ETSWP_get_cookie name='cookie name']
		add_shortcode( 'ETSWP_get_input' , array('EmogicTarotReader_Core','get_input') ); //[ETSWP_get_input name='cookie name'] for reading display page. intended for just ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
	}

	//build both emogic-database and emogic-readings options from wp pages
	public static function options(){ 
		$page_paths = [EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER , EMOGIC_TAROT_PLUGIN_READING_FOLDER];
		foreach($page_paths as $page_path){
			$html = '';
			$wp_post = get_page_by_path($page_path); //returns post object or null
			if(! isset($wp_post)){return;}//no $page_path stop everything
			$parent_id = $wp_post->ID;
			$page_path_parent = $page_path;
			$html = self::options_recursive_pages($parent_id,$page_path); //result will be the options text. then we can get rid off the fancy shortcode and simplify. no longer need $files
			wp_cache_set('ETSWP_'.$page_path.'_options' , $html);
			}
	}

	public static function options_recursive_pages($parent_id,$page_path_parent){ //note: recursive routine, do not change arg values here!!!
		$html = '';
		$html_children = '';
		$children = get_children( $parent_id );
		if( ! isset($children) ) {return $html;} //no branch $html = ''
		foreach ($children as $child) {
			if($page_path_parent == '') //root page
				$path = $child->post_title;
			else //not root, a sub page
				$path = $page_path_parent . '/' . $child->post_title;
			if( !(ctype_space($child->post_content) or ($child->post_content == '')) ){
				
				//not elegant. remove dom options display
				if( 0 === strpos($path , EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER ) ){
					$path_tmp = preg_replace('/^' . EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER . '\//', '', $path);
					$html = $html . "<option value='$path_tmp'>$path_tmp</option>";
					//$html = $html . "<option value='" . EMOGIC_TAROT_PLUGIN_WP_ROOT_URL . "/?page_id=" . $child->ID . "'>$path_tmp</option>";
				}
				if( 0 === strpos($path , EMOGIC_TAROT_PLUGIN_READING_FOLDER ) ){
					$path_tmp = preg_replace('/^' . EMOGIC_TAROT_PLUGIN_READING_FOLDER . '\//', '', $path);
					$perma = get_permalink($child->ID , false);
					//$perma = get_permalink($child->ID , true);
					$html = $html . "<option value='" . $perma . "'>$path_tmp</option>";
					//$html = $html . "<option value='" . EMOGIC_TAROT_PLUGIN_WP_ROOT_URL . "/?page_id=" . $child->ID . "'>$path_tmp</option>";
				}				
				//$perma = get_permalink($parent_id , false); //should we use this instead of
				//page_id will never change, $path_tmp should be viewer friendly (based on page title)
				//$html = $html . "<option value='" . EMOGIC_TAROT_PLUGIN_WP_ROOT_URL . "/?page_id=" . $child->ID . "'>$path_tmp</option>";
				//$html = $html . "<option value='$path_tmp'>$path_tmp</option>";
			}
			$html_children = self::options_recursive_pages($child->ID,$path);
			$html = $html . $html_children;
		}
		return $html;
	}

	//for quick short code retrieval
	public static function deck_options() {
		$page_path = EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER;
		$options = wp_cache_get('ETSWP_'.$page_path.'_options');
		return $options;
	}

	//for quick short code retrieval
	public static function spread_options() {
		$page_path = EMOGIC_TAROT_PLUGIN_READING_FOLDER;
		$options = wp_cache_get('ETSWP_'.$page_path.'_options');
		return $options;
	}

	public static function is_descendent_page_of( $path ){ //will only work when post is available. eg, after the_post hook
	//	global $post;
		$id = get_queried_object_id();
//		$ancs = get_ancestors($post->ID, 'page'); //get array of ancestor pages of current page
		$ancs = get_ancestors($id, 'page'); //get array of ancestor pages of current page
		if(count($ancs) == 0)
			return 0; //no ancestors
		$page_id =  get_page_by_path($path)->ID;
		if ( in_array($page_id , $ancs) || $post->post_parent == "$page_id" )
			return 1;
		else
			return 0;
	}

	public static function shuffle(){
	//if ( is_page('emogic-tarot') ) { //build options for main form page
		self::options(); //build options for main form page. Just always run it for all pages to avoid complex code
		//} 
	//if ( is_page('emogic-your-tarot-reading') ) {
		//self::options(); } //build options for page //need to do for shortcode
	if( ! self::is_descendent_page_of( EMOGIC_TAROT_PLUGIN_READING_FOLDER ) ) // no need to shuffle if not on a spread page
		//self::options();
	//else // no need to shuffle if not on a spread page
		return;

	//if here, we are a emogic-readings sub page
	//choose our deck
	//$deck_chosen = 'Emogic'; //default
	if( isset($_REQUEST["ETSWP_deck"]) ) {
		$deck_chosen = sanitize_text_field( $_REQUEST["ETSWP_deck"] );
		//strip ..
		$deck_chosen = str_replace( ".." , "" , $deck_chosen );
		}

	$wp_post = get_page_by_path(EMOGIC_TAROT_PLUGIN_DATABASE_FOLDER . '/' . $deck_chosen); //returns post object or null
	if(! isset( $wp_post )) {
		wp_die( "No Deck " . $deck_chosen , "No Deck" );
		} //if no deck stop everything.
	if( ctype_space($wp_post->post_content) or ($wp_post->post_content == '') ) {return;} //deck is empty or maybe just a directory

	//get deck text and put in array
	$file_string = $wp_post->post_content;
	$file_lines = preg_split("/\r\n|\n|\r/", $file_string); //$array = preg_split ('/$\R?^/m', $string);

	$ETSWP_items_array = array();
	$ETSWP_keys_shuffled = array();
	//$ETSWP_items_array will be complete array read in order.
	//we will shuffle a separate keys array, $ETSWP_keys_shuffled
	//then ensure that none of the key array points to another item in $ETSWP_items_array with a duplicate itemnumber

	//1st line is the column text description
	$line_string = array_shift( $file_lines );
	$columns_array = explode("|" , $line_string);

	$number_of_same_item_array = array(); //so we can see if this item has another version of it in the database, and roll to see which to keep

	//get all items in order
	while( count($file_lines) ){
		$line_string = array_shift( $file_lines );
		if( ctype_space($line_string) ){//ignore whitespace lines
			continue;
		}
		if($line_string == ''){//ignore empty lines
			continue;
		}
		$line_array = explode("|" , $line_string);
		$item_number = $line_array[0];
		$item_array = array_combine($columns_array , $line_array);

		array_push($ETSWP_items_array , $item_array);
		}

	$hash = self::build_cookie_name();
	if( isset($_COOKIE[$hash]) ){//simply convert cookie to cards
			$json = sanitize_text_field( $_COOKIE[$hash] );
			$ETSWP_keys_shuffled = json_decode($json);
		}
		else{//no cookies, shuffle cards
		//create a key array and shuffle it
		$ETSWP_keys_shuffled = array_keys($ETSWP_items_array); //$ETSWP_keys_shuffled is in order at this time
		shuffle($ETSWP_keys_shuffled);
		//remove keys that point to duplicate itemnumbers in $ETSWP_items_array
		$key_exists = array();
		foreach($ETSWP_keys_shuffled as $key){
			$itemnumber = $ETSWP_items_array[$key]['itemnumber'];
			if( isset( $key_exists[$itemnumber] ) ){
				unset($ETSWP_keys_shuffled[$key]); //remove item from $ETSWP_keys_shuffled array
				}
			$key_exists[$itemnumber] = 1;
			}
		//re-index $ETSWP_keys_shuffled as there are random holes in index
		$ETSWP_keys_shuffled = array_values($ETSWP_keys_shuffled);
		}

	wp_cache_set('ETSWP_items_array' , $ETSWP_items_array); //need to globalize it so we can use it in shortcode
	wp_cache_set('ETSWP_keys_shuffled' , $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode
	}

	//for quick short code retrieval
	public static function get_item( $atts = array(), $content = null ) {
		$ETSWP_items_array = wp_cache_get('ETSWP_items_array');
		$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');

		$item = $atts['item'] - 1; //the array starts at 0 so we want item 1 to point to that
		$column = $atts['column'];
		$output = $ETSWP_items_array[ $ETSWP_keys_shuffled[$item] ][$column];

		//first_name replace
		$first_name = 'Seeker';
		if( isset($_REQUEST["ETSWP_first_name"]) and ($_REQUEST["ETSWP_first_name"] != '') ) {
			$first_name = sanitize_text_field( $_REQUEST["ETSWP_first_name"] );
			}
		$output = str_replace( '[first_name]' , $first_name , $output );
		return $output;
		}

	//for quick short code retrieval
	public static function get_pluginpath( $atts = array(), $content = null ) {
		return EMOGIC_TAROT_PLUGIN_LOCATION_URL;
		}

	public static function set_tarot_shuffled_cards_cookie() {
		if ( isset($_GET['post_type'])  && $_GET['post_type'] === 'page' ){
			return;
			}//admin edit pages trigger this, why?

		//only do this if we are a child or grand child of spread page
		if( ! self::is_descendent_page_of( EMOGIC_TAROT_PLUGIN_READING_FOLDER ) )
			return;

		$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');

		$hash = self::build_cookie_name();
		if(!isset($_COOKIE[$hash])) {
			$json = json_encode($ETSWP_keys_shuffled); //save deck for specific ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
			setcookie($hash , $json , time()+(24*60*60) , "/" ); //cookie for a day
		}
	}

	//returns a  hash of form inputs of ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question']. this allows different readings for dirfferent names, questions, spreads
	//but also sets cookies for ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question']
	public static function build_cookie_name( $also_set_cookies = 0 ){ 
		$cookie_name = '';
		$cookie_array = ['ETSWP_first_name' , 'ETSWP_deck' , 'ETSWP_spread' , 'ETSWP_question'];
		foreach($cookie_array as $cookie){
			if( isset($_REQUEST[$cookie]) ){
				$cookie_name = $cookie_name . sanitize_text_field( $_REQUEST[$cookie] ); //build cookie name for card reading
				$result = setcookie( $cookie , sanitize_text_field( $_REQUEST[$cookie] ) , time()+(365*24*60*60) , "/" ); //save cookie of form fields from main tarot page
			}
		}
		$hash = hash( 'crc32' , $cookie_name ); //convert cookie name to hash
	return $hash; 
	}

	//for shortcode
	//[ETSWP_get_cookie name='ETSWP_deck']
	public static function get_cookie( $atts = array(), $content = null ){
		$name = $atts['name'];
		isset($_COOKIE[$name]) ? $cookie = sanitize_text_field( $_COOKIE[$name] ) : $cookie = '' ;
		return $cookie;
	}

	//for shortcode
	//[ETSWP_get_item item='3' column='itemimage']
	public static function get_input( $atts = array(), $content = null ){
		$name = $atts['name'];
		isset($_REQUEST[$name]) ? $input = sanitize_text_field( $_REQUEST[$name] ) : $input = '' ;
		return $input;
	}

}
