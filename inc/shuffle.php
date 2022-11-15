<?php
//as a class, not required
//ip: path to deck or deck data : cookies for daily cards
//op: shortcodes

//fix: maybe action on post , so we can determine what page we are on then only cache required??
//get file pages under folders: decks and spreads : needs to be accessed
function options(){
$page_paths = ['decks' , 'spreads'];
foreach($page_paths as $page_path){
	$wp_post = get_page_by_path($page_path); //returns post object or null
	if(! isset($wp_post)){return;}//no $page_path stop everything
	$parent_id = $wp_post->ID;
	$page_path_parent = $page_path;
	$html = options_recursive_pages($parent_id,''); //result will be the options text. then we can get rid off the fancy shortcode and simplify. no longer need $files

	wp_cache_set('ETSWP_'.$page_path.'_options' , $html);
	}
}

function options_recursive_pages($parent_id,$page_path_parent){ //note: recursive routine, do not change arg values here!!!
	$html = '';
	$html_children = '';
	$children = get_children( $parent_id );
	if( ! isset($children) ) {return $html;} //no branch $html = ''
	foreach ($children as $child) {
		if($page_path_parent == '') $path = $child->post_name;
		else $path = $page_path_parent . '/' . $child->post_name;
		$html = $html . "<option value='$path'>$path</option>";
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

//----------shuffle stuff

add_action( 'the_post', 'ETSWP_shuffle' ); //shuufle after we can determine if we are on a spread page
//add_shortcode( 'shuffle', 'ETSWP_shuffle' ); //cant run as shortcodes are async, and this is too slow!
function ETSWP_shuffle(){

if ( isset($_GET['action'])  && $_GET['action'] === 'edit' ){ options(); } //if we don't do this for edit pages, oddly enough shortcodes trigger and give errors.
if ( is_page('emogic-tarot') ) { options(); } //build options for page
if ( is_page('emogic-your-tarot-reading') ) { options(); } //build options for page //need to do for shortcode

//are we child of spreads? use this as options() has not run
global $post;
$ancs = get_ancestors($post->ID, 'page');
if(! isset($ancs[0])){$ancs[0]='foo';}
$spreads_page_id =  get_page_by_path('spreads')->ID;
if ( in_array($spreads_page_id , $ancs) || $post->post_parent == "$spreads_page_id" ) { options(); }
else{ return; }

//choose our deck
$deck_chosen = 'emogic'; //default, will normally be chosen by visitor
if( isset($_REQUEST["emogic_deck"]) ) {
    $deck_chosen = $_REQUEST["emogic_deck"];
	}
$wp_post = get_page_by_path('decks/'.$deck_chosen); //returns post object or null
if(! isset( $wp_post )) {return;} //if no deck stop everything.

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

if( isset($_COOKIE['ETSWP_items']) ){//simply convert cookie to cards
		$json = $_COOKIE['ETSWP_items'];
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
	//$this->ETSWP_items_array;
	$ETSWP_items_array = wp_cache_get('ETSWP_items_array');
	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');

	$item = $atts['item'] - 1; //the array starts at 0 so we want item 1 to point to that
	$column = $atts['column'];
	return $ETSWP_items_array[ $ETSWP_keys_shuffled[$item] ][$column];
	};

add_shortcode( 'pluginpath', 'pluginpath_function' );
function pluginpath_function( $atts = array(), $content = null ) {
	return EMOGIC_TAROT_PLUGIN_LOCATION_URL;
	};

//add_action( 'init', 'set_tarot_cookie'); //set out cookie at the appropriate time
add_action( 'the_post', 'set_tarot_cookie'); //set out cookie at the appropriate time, but late enough to have $post data
function set_tarot_cookie() {
	//only do this if we are on a spread page
	global $post;
	$ancs = get_ancestors($post->ID, 'page');
	if(! isset($ancs[0])){$ancs[0]='foo';}
	$spreads_page_id =  get_page_by_path('spreads')->ID;
	if (! ( in_array($spreads_page_id , $ancs) || $post->post_parent == "$spreads_page_id" ) ) { return; }
	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');
	$visit_time = date('F j, Y  g:i a');
	if(!isset($_COOKIE['ETSWP_items'])) {
		$json = json_encode($ETSWP_keys_shuffled);
		$foo = setcookie('ETSWP_items', $json , time()+(24*60*60) ); //cookie for a day
	}
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
