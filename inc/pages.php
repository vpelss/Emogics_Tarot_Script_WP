<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//pages can also be accessed ?pagename=parent-page/sub-page

add_action( 'wp_loaded', 'read_and_create_pages' ); //fix: just run when we delete a page and once on activate?
function read_and_create_pages(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	$page_path_parent = '';

	$deactivate_file_array = array(); //to use on deactivate so we can delete files plugin added
	add_pages($dir,$parent_id,$page_path_parent,$deactivate_file_array);//$pages is by reference
	wp_cache_set('ETSWP_deactivate_file_array' , array_reverse($deactivate_file_array)); //efficient, nope. but easy on the eyes
	$r= 6;
}

function add_pages($dir,$parent_id,$page_path_parent,&$deactivate_file_array){
	//note: recursive routine, do not change arg values here!!!
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		//does file exist?
		$page_path = $page_path_parent.'/'.$file;
		$wp_post = get_page_by_path($page_path); //returns post object or null
		if(is_file($dir.'/'.$file)){
			$post_status = 'draft';
			if(! $parent_id)
				$post_status = 'publish'; //only publish if root pages and files
			if( str_starts_with($page_path , '/spreads') )
				$post_status = 'publish'; //oh, and spreads too
			if(! isset($wp_post)) //file does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			array_push( $deactivate_file_array , $page_path_parent.'/'.$file );
		}
		if(is_dir($dir.'/'.$file)){
			//create empty page
			$post_status = 'draft';
			if(isset($wp_post)){//dir exists
				$parent_id_temp = $wp_post->ID;
			}
			else{//dir does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			}
			array_push( $deactivate_file_array , $page_path_parent.'/'.$file );
			add_pages($dir.'/'.$file , $parent_id_temp , $page_path_parent.'/'.$file , $deactivate_file_array);
		}
	}
	return;
}

function post_page_if_required( $post_parent , $page_path_parent , $file_name , $dir , $post_status){//$file is pagename, $path is page path with parents, $data is data for the page
		if( is_file($dir.'/'.$file_name ) ){	$data = file_get_contents($dir.'/'.$file_name , true); }
		else{ $data = ''; } //dir has no data
		//create page
		$postarr  = array(
			//'ID' => $page_id,
			'post_title'    => $file_name, //can have spaces
			'post_name' => $file_name, //slug, will have - not spaces
			'post_content'  => $data,
			'post_status'   => $post_status,
			'post_type' => 'page',
			'post_parent' => $post_parent
			);
		return wp_insert_post( $postarr ); //returns page_id
}

?>
