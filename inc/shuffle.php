<?php

//test
/*
$number_of_people = 10;
$number_or_runs = 1000;
$wins = array();

for ($run = 1 ; $run <= $number_or_runs ; $run++){
        $last_winner = 1;
        for ($person = 1 ; $person <= $number_of_people ; $person++){
                $win = rand( 1 , $person);
                if($win == 1){
                        $last_winner = $person;
                }

        }
        if( ! isset( $wins[$last_winner] ) ) {
                $wins[$last_winner] = 0;
                }
        $wins[$last_winner]++;
}
*/

$dir = EMOGIC_TAROT_PLUGIN_PATH . "/decks/";
$file_name = "emogic";
$file_string = file_get_contents($dir . $file_name , true);

$file = fopen($dir . $file_name , "r");
$items_array = array();

$line_string = fgets($file);
$columns_array = explode("|" , $line_string);
array_shift($columns_array); //get rid of itemnumber as we are going to use it in the associative array
$number_of_same_item_array = array(); //so we can see if this item has another version of it in the database, and roll to see which to keep

while(! feof($file)){
	$line_string = fgets($file);
	if( ctype_space($line_string) ){//ignore whitespace lines
		continue;
	}
	if($line_string == ''){//ignore empty lines
		continue;
	}
	$line_array = explode("|" , $line_string);
	$item_number = array_shift($line_array);
	$item_array = array_combine($columns_array , $line_array);

	//choose and add item to $items_array
	$number_of_same_item_array[$item_number]++;
	$win = rand( 1 , $number_of_same_item_array[$item_number]); //the new item will win if $win = 1. It LOOKS like a less probability, but the probability is the same as the other items overall
	if( ($win == 1) || ($number_of_same_item_array[$item_number] == 1) ){//use new value
		$items_array[$item_number] = $item_array;
		}
	//array_push( $items_array , $item_array);
	}
fclose($file);

shuffle($items_array); //note that keys have been replaced and now start at 0!

$r = 8;

?>
