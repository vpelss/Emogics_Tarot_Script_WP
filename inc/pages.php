<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

add_action( 'wp_loaded', 'read_and_create_pages2' ); //maybe move to activate.php later

function read_and_create_pages2(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	add_pages($dir,$parent_id);
}

function add_pages($dir,$parent_id){
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		if(is_file($file)){
			$r =88;
			$parent_id_temp = post_page_if_required2();
		}
		if(is_dir($file)){
			$r=76;
			//create empty page
			$parent_id_temp = post_page_if_required2();
			add_pages($dir.'/'.$file , $parent_id_temp);
		}

		//$data = file_get_contents($dir . $file , true);
		//$post_id = post_page_if_required( 0 , $file , '' , $dir , 1);
		//if($post_id) wp_publish_post( $post_id );
		}
	return;
}

function post_page_if_required2( $post_parent_ID , $file , $path_parent , $dir , $load_data = 0){//$file is pagename, $path is page path with parents, $data is data for the page
		/*
		$path_file = $path_parent . '/' . $file;
		$page_test = get_page_by_path($path_file); //post object or null
		if( $page_test ){return 0;} //skip if this file page already exists
		if($load_data){
			$data = file_get_contents($dir . $file , true);
		}
		else{
			$data = '';
		}
		//create
		$wordpress_page = array(
			//'ID' => $page_id,
			'post_title'    => $file,
			'post_name' => $file,
			'post_content'  => $data,
			'post_status'   => 'draft',
			'post_type' => 'page',
			'post_parent' => $post_parent_ID
			);
		return wp_insert_post( $wordpress_page );
		*/
}

//load default pages and create them if they do not already exist/ place them as sub pages under main tarot page
function read_and_create_pages(){
//load main pages
$folder = 'pages';
$dir = EMOGIC_TAROT_PLUGIN_PATH . "/" . $folder . "/";
$files = scandir($dir);
foreach ($files as $file) {
		if($file == "."){continue;}
		if($file == ".."){continue;}

		$data = file_get_contents($dir . $file , true);
		$post_id = post_page_if_required( 0 , $file , '' , $dir , 1);
		if($post_id) wp_publish_post( $post_id );
		}

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
		$page_test = get_page_by_path($path_file); //post object or null
		if( $page_test ){return 0;} //skip if this file page already exists
		if($load_data){
			$data = file_get_contents($dir . $file , true);
		}
		else{
			$data = '';
		}
		//create
		$wordpress_page = array(
			//'ID' => $page_id,
			'post_title'    => $file,
			'post_name' => $file,
			'post_content'  => $data,
			'post_status'   => 'draft',
			'post_type' => 'page',
			'post_parent' => $post_parent_ID
			);
		return wp_insert_post( $wordpress_page );
}

?>
