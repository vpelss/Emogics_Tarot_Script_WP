<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

add_action( 'wp_loaded', 'read_and_create_pages' ); //maybe move to activate.php later

//load default pages and create them if they do not already exist/ place them as sub pages under main tarot page
function read_and_create_pages(){
//load main page
$path_parent = 'emogic-tarot';
$post_parent = get_page_by_path($path_parent , OBJECT , 'page');
$folder = 'pages';
$dir = EMOGIC_TAROT_PLUGIN_PATH . "/" . $folder . "/";
$file = 'Emogic Tarot';
$data = file_get_contents($dir . $file , true);

post_page_if_required( 0 , $file , '' , $dir , 1);
//post_page_if_required( 0 , $file , '' , $data);

//load subfolders and contents
$deck_folder = 'Decks';
$spread_folder = 'Spreads';
$folders = array( $deck_folder , $spread_folder );
foreach( $folders as $folder ){
	//create deck/spread parent page if required
	$path_parent = 'emogic-tarot';
	$post_parent = get_page_by_path($path_parent , OBJECT , 'page');
	$post_parent_ID = $post_parent->ID;
	post_page_if_required( $post_parent_ID , $folder , $path_parent , '' , 0);
	//post_page_if_required( $post_parent_ID , $folder , $path_parent );
	//get files in folders
	$path_parent = $path_parent . '/'. $folder;
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/" . $folder . "/";
	$files = scandir($dir);
	$post_parent = get_page_by_path($path_parent , OBJECT , 'page');
	$post_parent_ID = $post_parent->ID;
	foreach ($files as $file) {
		if($file == "."){continue;}
		if($file == ".."){continue;}
		//$data = file_get_contents($dir . $file , true);
		//post_page_if_required( $post_parent_ID , $file , $path_parent , $data);
		post_page_if_required( $post_parent_ID , $file , $path_parent , $dir , 1);
		}
	}
}

function post_page_if_required( $post_parent_ID , $file , $path_parent , $dir , $load_data = 0){//$file is pagename, $path is page path with parents, $data is data for the page
		$path_file = $path_parent . '/' . $file;
		$page_test = get_page_by_path($path_file);
		if( $page_test ){return 0;} //skip if this file page already exists
		//create page
		if($load_data){
			$data = file_get_contents($dir . $file , true);
		}
		$wordpress_page = array(
			'post_title'    => $file,
			'post_name' => $file,
			'post_content'  => $data,
			'post_status'   => 'draft',
			'post_type' => 'page',
			'post_parent' => $post_parent_ID
			);
		wp_insert_post( $wordpress_page );
}

?>
