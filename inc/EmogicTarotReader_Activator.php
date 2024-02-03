<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class EmogicTarotReader_Activator{

	public static function activate(){
		self::read_and_create_pages();
		flush_rewrite_rules();
	}

	public static function read_and_create_pages(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	$page_path_parent = '';

	$deactivate_file_array = array(); //to use on deactivate so we can delete files plugin added
	self::add_pages($dir,$parent_id,$page_path_parent,$deactivate_file_array);//$pages is by reference
	add_option('EmogicTarotReader_option_deactivate_file_array' , array_reverse($deactivate_file_array));
	}

	public static function add_pages($dir,$parent_id,$page_path_parent,&$deactivate_file_array){
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
				$parent_id_temp =  self::post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			array_push( $deactivate_file_array , $page_path_parent.'/'.$file );
		}
		if(is_dir($dir.'/'.$file)){ //create empty page
			$post_status = 'draft';
			if(isset($wp_post)){//dir exists
				$parent_id_temp = $wp_post->ID;
			}
			else{//dir does not exist
				$parent_id_temp =  self::post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			}
			array_push( $deactivate_file_array , $page_path_parent.'/'.$file );
			self::add_pages($dir.'/'.$file , $parent_id_temp , $page_path_parent.'/'.$file , $deactivate_file_array);
		}
	}
	return;
	}

	public static function post_page_if_required( $post_parent , $page_path_parent , $file_name , $dir , $post_status){//$file is pagename, $path is page path with parents, $data is data for the page
		if( is_file($dir.'/'.$file_name ) ){	$data = file_get_contents($dir.'/'.$file_name , true); }
		else{ $data = ''; } //this is a directory. it has  no data
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

}
