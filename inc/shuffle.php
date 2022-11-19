<?php
//as a class, not required
//ip: path to deck or deck data : cookies for daily cards
//op: shortcodes

//fix: maybe action on post , so we can determine what page we are on then only cache required??
//get file pages under folders: decks and spreads : needs to be accessed
function options(){
$page_paths = ['decks' , 'spreads'];
foreach($page_paths as $page_path){
	$html = '';
	$wp_post = get_page_by_path($page_path); //returns post object or null
	if(! isset($wp_post)){return;}//no $page_path stop everything
	$parent_id = $wp_post->ID;
	$page_path_parent = $page_path;
	$html = options_recursive_pages($parent_id,$page_path); //result will be the options text. then we can get rid off the fancy shortcode and simplify. no longer need $files
	wp_cache_set('ETSWP_'.$page_path.'_options' , $html);
	}
}

function options_recursive_pages($parent_id,$page_path_parent){ //note: recursive routine, do not change arg values here!!!
	$html = '';
	$html_children = '';
	$children = get_children( $parent_id );
	if( ! isset($children) ) {return $html;} //no branch $html = ''
	foreach ($children as $child) {
		if($page_path_parent == '') $path = $child->post_title;
		else $path = $page_path_parent . '/' . $child->post_title;
		if( !(ctype_space($child->post_content) or ($child->post_content == '')) ){
			//not elegant. remove drom options display
			$path_tmp = preg_replace('/^decks\//', '', $path);
			$path_tmp = preg_replace('/^spreads\//', '', $path_tmp);

			$html = $html . "<option value='$path_tmp'>$path_tmp</option>";
		}
		$html_children = options_recursive_pages($child->ID,$path);
		$html = $html . $html_children;
	}
	return $html;
}

add_shortcode( 'ETSWP_deck_options', 'ETSWP_deck_options_function' ); //for main page
function ETSWP_deck_options_function() {
	$page_path = 'decks';
	$options = wp_cache_get('ETSWP_'.$page_path.'_options');
	return $options;
}

add_shortcode( 'ETSWP_spread_options', 'ETSWP_spread_options_function' ); //for main page
function ETSWP_spread_options_function() {
	$page_path = 'spreads';
	$options = wp_cache_get('ETSWP_'.$page_path.'_options');
	return $options;
}

function is_descendent_page_of( $path ){ //will only work when post is available. eg, after the_post hook
	global $post;
	$ancs = get_ancestors($post->ID, 'page'); //get array of ancestor pages of current page
	if(count($ancs) == 0)
		return 0; //no ancestors
	$page_id =  get_page_by_path($path)->ID;
	if ( in_array($page_id , $ancs) || $post->post_parent == "$page_id" )
		return 1;
	else
		return 0;
}

//----------shuffle stuff

add_action( 'the_post', 'ETSWP_shuffle' ); //shuufle after we can determine if we are on a spread page
//add_shortcode( 'shuffle', 'ETSWP_shuffle' ); //cant run as shortcodes are async, and this is too slow!
function ETSWP_shuffle(){

if ( isset($_GET['action'])  && $_GET['action'] === 'edit' ){ options(); } //if we don't do this for edit pages, oddly enough shortcodes trigger and give errors.
if ( is_page('emogic-tarot') ) { options(); } //build options for page
if ( is_page('emogic-your-tarot-reading') ) { options(); } //build options for page //need to do for shortcode
if( is_descendent_page_of( 'spreads' ) )
	options();
else // no need to shuffle
	return;

//choose our deck
$deck_chosen = 'emogic'; //default, will normally be chosen by visitor
if( isset($_REQUEST["emogic_deck"]) ) {
    $deck_chosen = $_REQUEST["emogic_deck"];
	}
$wp_post = get_page_by_path('decks/'.$deck_chosen); //returns post object or null
if(! isset( $wp_post )) {return;} //if no deck stop everything.
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

$hash = build_cookie_name();
if( isset($_COOKIE[$hash]) ){//simply convert cookie to cards
		$json = $_COOKIE[$hash];
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

//this is how we place cards on spreads [ETSWP item='1' column='itemname']
add_shortcode( 'ETSWP', 'ETSWP_function' );
function ETSWP_function( $atts = array(), $content = null ) {
	$ETSWP_items_array = wp_cache_get('ETSWP_items_array');
	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');

	$item = $atts['item'] - 1; //the array starts at 0 so we want item 1 to point to that
	$column = $atts['column'];
	$output = $ETSWP_items_array[ $ETSWP_keys_shuffled[$item] ][$column];

	//first_name replace
	$first_name = 'Seeker';
	//$r = $_REQUEST["first_name"];
	if( isset($_REQUEST["first_name"]) and ($_REQUEST["first_name"] != '') ) {
		$first_name = $_REQUEST["first_name"];
		}
	$output = str_replace( '[first_name]' , $first_name , $output );

	return $output;
	};

add_shortcode( 'pluginpath', 'pluginpath_function' );
function pluginpath_function( $atts = array(), $content = null ) {
	return EMOGIC_TAROT_PLUGIN_LOCATION_URL;
	};

//add_action( 'init', 'set_tarot_cookie'); //set out cookie at the appropriate time
add_action( 'the_post', 'set_tarot_cookie'); //set out cookie at the appropriate time, but late enough to have $post data
function set_tarot_cookie() {
	if ( isset($_GET['post_type'])  && $_GET['post_type'] === 'page' ){ return; }//admin edit pages trigger this, why?

	//only do this if we are a child or grand child of spread page
	if( ! is_descendent_page_of( 'spreads' ) )
		return;

	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');

	$hash = build_cookie_name();
	if(!isset($_COOKIE[$hash])) {
		$json = json_encode($ETSWP_keys_shuffled); //save deck for specific ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question']
		setcookie($hash , $json , time()+(24*60*60) ); //cookie for a day
		//$ETSWP_readings[$hash]['keys_shuffled'] = $json;
		//$ETSWP_readings[$hash]['timestamp'] = time();
		//add_option( 'ETSWP_readings' , $ETSWP_readings ); //save reading as sql backup incase same reading on other device or no cookies were tossed
	}
}

function build_cookie_name( $also_set_cookies = 0 ){
	$cookie_name = '';
	$cookie_array = ['first_name' , 'emogic_deck' , 'emogic_spread' , 'emogic_question'];
	foreach($cookie_array as $cookie){
		if( isset($_REQUEST[$cookie]) ){
			$cookie_name = $cookie_name . $_REQUEST[$cookie]; //build cookie name for card reading
			setcookie( 'ETSWP_'.$cookie , $_REQUEST[$cookie] , time()+(365*24*60*60) ); //save cookie of form field
		}
	}
	$hash = hash( 'crc32' , $cookie_name ); //convert cookie name to hash
return $hash;
}

add_shortcode( 'cookie', 'cookie_function' ); //for reading display page
function cookie_function( $atts = array(), $content = null ){
	$name = $atts['name'];
	//$r = $_COOKIE;
	isset($_COOKIE[$name]) ? $cookie = $_COOKIE[$name] : $cookie = '' ;
	return $cookie;
}
/*
add_shortcode( 'spread', 'spread_function' ); //for reading display page
function spread_function(){
$files = wp_cache_get('ETSWP_spreads');
$spread = 'three-card';

do this another way. just download it here once when required. sure we have already done it in in options, but simplify
$wp_children = wp_cache_get('ETSWP_wp_children_spreads' );

//$page_id = $files[$spread];
//$page =  $wp_children[ $files[$spread] ];
//$html = $page->post_content;
$html2 = $wp_children[ $files[$spread] ]->post_content;
$html = do_shortcode ( $html2 ); //not working
return $html;
}
*/

?>
