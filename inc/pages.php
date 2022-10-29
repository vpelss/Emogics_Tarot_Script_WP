<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//add_action('init', 'imok_read_and_create_pages'); //if we want to continually check for and add missing pages....

//load default pages and create them if they do not already exist.
function imok_read_and_create_pages(){

$dir = IMOK_PLUGIN_PATH . "/pages/";
$files = scandir($dir);

foreach ($files as $file) {
	if($file == "."){continue;}
	if($file == ".."){continue;}
	if( get_page_by_title($file) ){continue;} //skip if this file page already exists
	$file_string = file_get_contents($dir . $file , true);

	$wordpress_page = array(
		'post_title'    => $file,
		'post_content'  => $file_string,
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type' => 'page'
		);
	wp_insert_post( $wordpress_page );
	}
}

?>
