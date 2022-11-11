<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

add_action( 'wp_loaded', 'read_and_create_pages' ); //maybe move to enable later
//add_action( 'wp_loaded', array($this,read_and_create_pages) ); //maybe move to enable later

//load default pages and create them if they do not already exist/ place them as sub pages under main tarot page
function read_and_create_pages(){

$deck_folder = 'Decks';
$spread_folder = 'Spreads';

$folders = array( $deck_folder , $spread_folder );
foreach( $folders as $folder ){
	//create deck/spread parent page if required
	$path = 'emogic-tarot';
	$post_parent = get_page_by_path($path , OBJECT , 'page');

	$path = $path . '/'. $folder;
	$page_test = get_page_by_path($path);
	if( ! $page_test ){
		$wordpress_page = array(
			'post_title'    => $folder,
			'post_name' => $folder,
			'post_content'  => '',
			'post_status'   => 'draft',
			'post_type' => 'page',
			'post_parent' => $post_parent->ID
			);
		wp_insert_post( $wordpress_page );
		}

	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/" . $folder . "/";
	$files = scandir($dir);
	$post_parent = get_page_by_path($path , OBJECT , 'page');
	foreach ($files as $file) {
		if($file == "."){continue;}
		if($file == ".."){continue;}

		$path_file = $path . '/' . $file;
		$page_test = get_page_by_path($path_file);

		if( $page_test ){continue;} //skip if this file page already exists
		//create page
		$file_string = file_get_contents($dir . $file , true);
		$wordpress_page = array(
			'post_title'    => $file,
			'post_name' => $file,
			'post_content'  => $file_string,
			'post_status'   => 'draft',
			'post_type' => 'page',
			'post_parent' => $post_parent->ID
			);
		wp_insert_post( $wordpress_page );
		}
	}
}

?>
