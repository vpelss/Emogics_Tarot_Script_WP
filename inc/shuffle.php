<?php

$dir = EMOGIC_TAROT_PLUGIN_PATH . "/decks/";
$file_name = "emogic";
$file_string = file_get_contents($dir . $file_name , true);

$file = fopen($dir . $file_name , "r");
$deck_array = array();

$line_string = fgets($file);
$columns_array = explode("|" , $line_string);

while(! feof($file)){
	$line_string = fgets($file);
	if( ctype_space($line_string) ){
		continue;
	}
	if($line_string == ''){
		continue;
	}
	$line_array = explode("|" , $line_string);
	$item_array =array_combine($columns_array , $line_array);
	array_push( $deck_array , $item_array);
	}

fclose($file);

//create an array 1 .. last same length as deck. then shuffle array

$r = 8;

?>
