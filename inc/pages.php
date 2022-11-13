<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

add_action( 'wp_loaded', 'read_and_create_pages' ); //fix: just run when we delete a page and once on activate?

//pages can also be accessed ?pagename=parent-page/sub-page

function read_and_create_pages(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	$page_path_parent = '';
	add_pages($dir,$parent_id,$page_path_parent);
}

function add_pages($dir,$parent_id,$page_path_parent){
	//note: recursive routine, do not change arg values here!!!
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {

		//does file exist?
		$page_path = $page_path_parent.'/'.$file;
		$wp_post = get_page_by_path($page_path); //returns post object or null
		if(is_file($dir.'/'.$file)){
			$post_status = 'draft';
			if(! $parent_id){$post_status = 'publish';}//only publish if root pages and files
			$load_data = 1;
			if(! isset($wp_post)) //file does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status , $load_data);
		}
		if(is_dir($dir.'/'.$file)){
			//create empty page
			$post_status = 'draft';
			$load_data = 0;
			if(isset($wp_post)){//dir exists
				$parent_id_temp = $wp_post->ID;
			}
			else{//dir does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status , $load_data );
			}
			add_pages($dir.'/'.$file , $parent_id_temp,$page_path_parent.'/'.$file);
		}
	}
	return;
}

function post_page_if_required( $post_parent , $page_path_parent , $file_name , $dir , $post_status , $load_data = 0){//$file is pagename, $path is page path with parents, $data is data for the page
		//get file data, or not
		if($load_data){	$data = file_get_contents($dir.'/'.$file_name , true); }
		else{ $data = ''; }
		//create page
		$postarr  = array(
			//'ID' => $page_id,
			'post_title'    => $file_name,
			'post_name' => $file_name,
			'post_content'  => $data,
			'post_status'   => $post_status,
			'post_type' => 'page',
			'post_parent' => $post_parent
			);
		return wp_insert_post( $postarr ); //returns page_id
}

?>
