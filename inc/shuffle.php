<?php

//as a class, not required
//ip: path to deck or deck data : cookies for daily cards
//op: shortcodes

//fix: maybe action on post , so we can determine what page we are on then only cache required??
//get file pages under folders: decks and spreads : needs to be accessed / called by spreads also so ether require_once in spreads.php or other option?
function options(){
$page_paths = ['decks' , 'spreads'];
foreach($page_paths as $page_path){
	$wp_post = get_page_by_path($page_path); //returns post object or null
	if(! isset($wp_post)){return;}//no $page_path stop everything
	$wp_children[$page_path] = get_children( $wp_post->ID );
	//do we need to download the objects?
	//$wp_children[$page_path] = get_children( [	'post_parent' => $wp_post->ID , 'fields' => 'ids', ]);
	//$wp_children[$page_path] = get_children( [	'post_parent' => $wp_post->ID , 'fields' => 'id=>parent', ]);
	//$wp_children[$page_path] = get_children( [	'post_parent' => $wp_post->ID , 'fields' => 'post_name', ]);
	//get_children( [	'post_parent' 	=> $post_id, 'fields'        => 'ids', ] ); post_name id=>parent
	//get_the_title

	wp_cache_set('ETSWP_wp_children_'.$page_path , $wp_children[$page_path]); //need for shuffle and spreads shortcodes on main page
	//build associative array : keys $filenames , value page_id
	$files[$page_path] = array();
	foreach($wp_children[$page_path] as $wp_post){
		$files[$page_path][$wp_post->post_name] = $wp_post->ID;
	}
	//save $files[$page_path] with page_ids to 'ETSWP_'.$page_path
	wp_cache_set('ETSWP_'.$page_path , $files[$page_path]); //uesd to build select drop-downs in shortcodes and also to find deck page (already in memory) below
	}
}

add_shortcode( 'ETSWP_deck_options', 'ETSWP_deck_options_function' ); //for main page
function ETSWP_deck_options_function() {
	$page_path = 'decks';
	$files = wp_cache_get('ETSWP_'.$page_path);
	$keys= array_keys($files);
	$html = '';
	foreach($keys as $file){
		$html = $html .  "<option value='$files[$file]'>$file</option>";
	}
	return $html;
}

add_shortcode( 'ETSWP_spread_options', 'ETSWP_spread_options_function' ); //for main page
function ETSWP_spread_options_function() {
	$page_path = 'spreads';
	$files = wp_cache_get('ETSWP_'.$page_path);
	$keys= array_keys($files);
	$html = '';
	foreach($keys as $file){
		$html = $html .  "<option value='$files[$file]'>$file</option>";
	}
	return $html;
}

//----------shuffle stuff

add_action( 'the_post', 'ETSWP_shuffle' ); //shuufle after we can determine if we are on a spread page
//add_shortcode( 'shuffle', 'ETSWP_shuffle' ); //cant run as shortcodes are async, and this is too slow!
function ETSWP_shuffle(){

if ( is_page('emogic-tarot') ) { options(); } //build options for page

//are we child of spreads?
$ancs = get_ancestors($post->ID, 'page');
if(! isset($ancs[0])){$ancs[0]='foo';}
$spreads_page_id =  get_page_by_path('spreads')->ID;
if ( $ancs[0] == $spreads_page_id || $post->post_parent == "$spreads_page_id" ) { options(); }
else{ return; }

//choose our deck
$deck_chosen = 'emogic'; //default, will normally be chosen by visitor
if( isset($_REQUEST["deck"]) ) {
    $deck_chosen = $_REQUEST["deck"];
	}
$wp_post = get_page_by_path('decks/'.$deck_chosen); //returns post object or null
if(! isset( $wp_post )) {return;} //if no deck stop everything.

//get deck text and put in array
$file_string = $wp_post->post_content;
$file_lines = preg_split("/\r\n|\n|\r/", $file_string); //$array = preg_split ('/$\R?^/m', $string);

$ETSWP_items_array = array(); //will be complete array read in order.
//we will shuffle a separate keys array,
//then ensure that none of the key array points to another item in $ETSWP_items_array with a duplicate itemnumber

$ETSWP_keys_shuffled = array();

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

add_action( 'init', 'set_tarot_cookie'); //set out cookie at the appropriate time
function set_tarot_cookie() {
	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');
	$visit_time = date('F j, Y  g:i a');
	if(!isset($_COOKIE['ETSWP_items'])) {
		$json = json_encode($ETSWP_keys_shuffled);
		$foo = setcookie('ETSWP_items', $json , time()+(24*60*60) ); //cookie for a day
	}
}

add_shortcode( 'spread', 'spread_function' ); //for reading display page
function spread_function(){

$files = wp_cache_get('ETSWP_spreads');
$spread = 'three-card';
$wp_children = wp_cache_get('ETSWP_wp_children_spreads' );
//$page_id = $files[$spread];
//$page =  $wp_children[ $files[$spread] ];
//$html = $page->post_content;
$html = $wp_children[ $files[$spread] ]->post_content;
return $html;
}

?>
