<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//pages can also be accessed ?pagename=parent-page/sub-page

add_action( 'wp_loaded', 'read_and_create_pages' ); //fix: just run when we delete a page and once on activate?
function read_and_create_pages(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	$page_path_parent = '';

	//$pages = array();//will have $pages['file_paths'] (an array) and  $pages['dir_paths'] (an array) so we can ignore dir in options build. save wp_cache
	//$pages['file_paths'] = [];
	//$pages['dir_paths'] = [];

	add_pages($dir,$parent_id,$page_path_parent);//$pages is by reference

	//wp_cache_set('ETSWP_pages' , $pages);
	$r = 9;
}

function add_pages($dir,$parent_id,$page_path_parent){
	//note: recursive routine, do not change arg values here!!!
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		//does file exist?
		$page_path = $page_path_parent.'/'.$file;
		$wp_post = get_page_by_path($page_path); //returns post object or null
		if(is_file($dir.'/'.$file)){

			//array_push( $pages['file_paths'] , ltrim($page_path, '/') );

			$post_status = 'draft';
			if(! $parent_id)
				$post_status = 'publish'; //only publish if root pages and files
			if( str_starts_with($page_path , '/spreads') )
				$post_status = 'publish'; //oh, and spread

			//if( is_descendent_page_of( 'spreads' ) )
				//$post_status = 'publish';

			//$load_data = 1;
			if(! isset($wp_post)) //file does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
		}
		if(is_dir($dir.'/'.$file)){
			//create empty page

			//array_push( $pages['dir_paths'] , ltrim($page_path, '/') );

			$post_status = 'draft';
			//$load_data = 0;
			if(isset($wp_post)){//dir exists
				$parent_id_temp = $wp_post->ID;
			}
			else{//dir does not exist
				$parent_id_temp =  post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			}
			add_pages($dir.'/'.$file , $parent_id_temp,$page_path_parent.'/'.$file);
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
