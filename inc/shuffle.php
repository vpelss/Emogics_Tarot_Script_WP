<?php

//as a class, not required
//ip: path to deck or deck data : cookies for daily cards
//op: shortcodes

$dir = EMOGIC_TAROT_PLUGIN_PATH . "/decks/";
$file_name = "emogic";
$file_string = file_get_contents($dir . $file_name , true);

$file = fopen($dir . $file_name , "r");

$ETSWP_items_array = array(); //will be complete array read in order.
//we will shuffle a separate keys array,
//then ensure that none of the key array points to another item in $ETSWP_items_array with a duplicate itemnumber

$ETSWP_keys_shuffled = array();

//1st line is the column text description
$line_string = trim( fgets($file) );
$columns_array = explode("|" , $line_string);

$number_of_same_item_array = array(); //so we can see if this item has another version of it in the database, and roll to see which to keep

//get all items in order
while(! feof($file)){
	$line_string = trim( fgets($file) );
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
fclose($file);

if(!isset($_COOKIE['ETSWP_items'])) {//no cookies, shuffle cards
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
	else{//simply convert cookie to cards
		$json = $_COOKIE['ETSWP_items'];
		$ETSWP_keys_shuffled = json_decode($json);
	}

wp_cache_set('ETSWP_items_array' , $ETSWP_items_array); //need to globalize it so we can use it in shortcode
wp_cache_set('ETSWP_keys_shuffled' , $ETSWP_keys_shuffled); //need to globalize it so we can use it in shortcode

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

add_action( 'init', 'set_tarot_cookie');
function set_tarot_cookie() {
	$ETSWP_keys_shuffled = wp_cache_get('ETSWP_keys_shuffled');
	$visit_time = date('F j, Y  g:i a');
	if(!isset($_COOKIE['ETSWP_items'])) {
		$json = json_encode($ETSWP_keys_shuffled);
		$foo = setcookie('ETSWP_items', $json , time()+(24*60*60) ); //cookie for a day
	}
}

?>
