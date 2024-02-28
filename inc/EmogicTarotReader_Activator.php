<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//read all files, folders in /pages
//you can have multiple folder levels, but not recommended. eg: /pages/databases/runes/(runedb)
//create pages for all files and folders following directory structure
//page name will be same as folder or file name
//only root folder files will be published. the databases are in draft as some may not want their databases exposed

class EmogicTarotReader_Activator{
	
	public static function activate(){
		self::read_and_create_pages();
		self::images_to_media_library();
		flush_rewrite_rules();
	}
	
	public static function read_and_create_pages(){
		$dir = EMOGIC_TAROT_PLUGIN_PATH . "pages/";
		$parent_id = 0;
		$page_path_parent = '/';
	
		$deactivate_file_array = array(); //to use on deactivate so we can delete files plugin added
		self::add_pages($dir , $parent_id , $page_path_parent , $deactivate_file_array);//$pages is by reference		
		add_option(EMOGIC_TAROT_PLUGIN_PAGES_ARRAY_OPTION , array_reverse($deactivate_file_array)); //store them in wp db
	}

	public static function add_pages($dir , $parent_id , $page_path_parent , &$deactivate_file_array){
	//note: recursive routine, do not change arg values here!!!
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		//does file exist?
		$page_path = $page_path_parent . $file;
		$wp_post = get_page_by_path($page_path); //we will see if it exists later so we do not overwrite it!
		if(is_file($dir . $file)){ //files
			$post_status = 'draft'; //assume is db 
			if($parent_id == 0)  // on start we set $parent_id = 0 : publish root pages
				$post_status = 'publish';
			if( str_starts_with($page_path , '/' . EMOGIC_TAROT_PLUGIN_READING_FOLDER ) ) //files under EMOGIC_TAROT_PLUGIN_READING_FOLDER are published
				$post_status = 'publish'; 
			if(! isset($wp_post)) //file does not exist
				$parent_id_temp =  self::post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			array_push( $deactivate_file_array , $page_path_parent . $file );
		}
		if(is_dir($dir . $file)){ //for directories create empty page
			$post_status = 'draft';
			if(isset($wp_post)){//dir exists
				$parent_id_temp = $wp_post->ID;
			}
			else{//dir does not exist
				$parent_id_temp =  self::post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			}
			array_push( $deactivate_file_array , $page_path_parent . $file );
			self::add_pages($dir . $file . '/' , $parent_id_temp , $page_path_parent . $file . '/' , $deactivate_file_array);
		}
	}
	return;
	}

	public static function post_page_if_required( $post_parent , $page_path_parent , $file_name , $dir , $post_status){//$file is pagename, $path is page path with parents, $data is data for the page
		if( is_file($dir . $file_name ) ){
			$data = file_get_contents($dir . $file_name , true);
			}
		else{
			$data = '';
			} //this is a directory. it has  no data
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
	
	public static function images_to_media_library(){
		$deactivate_media_array = array();
		$from = EMOGIC_TAROT_PLUGIN_PATH . "images/";
		$to = get_home_path() . "wp-content/uploads/" .EMOGIC_TAROT_PLUGIN_MEDIA_FOLDER . "/";
		
		@mkdir($to, 0755); //create dest dir
		self::recursive_copy($from , $to , $deactivate_media_array); //copy files		
		add_option( EMOGIC_TAROT_PLUGIN_MEDIA_ARRAY_OPTION , array_reverse($deactivate_media_array));	
	}
	
	public static function recursive_copy($src , $dst , &$deactivate_media_array) { //$src and $dst must have lagging slashes
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . $file) ) { 
					self::recursive_copy($src . $file . '/' , $dst . $file . '/' , $deactivate_media_array); 
				} 
				else { 
					copy($src . $file , $dst . $file);
					self::copy_image_to_media_library($dst . $file , $file , $deactivate_media_array);
				} 
			} 
		} 
		closedir($dir); 
	}

	public static function copy_image_to_media_library($file_path , $filename , &$deactivate_media_array){			 
			$artdata = array(
				'post_author' => 1, 
				//'post_date' => current_time('mysql'),
				//'post_date_gmt' => current_time('mysql'),
				'post_title' => $filename, 
				//'post_status' => 'inherit',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $filename)),											'post_modified' => current_time('mysql'),
				'post_modified_gmt' => current_time('mysql'),
				'post_parent' => 0,
				'post_type' => 'attachment',
				//'guid' => $siteurl.'/'.$artDir.$new_filename,
				'guid' => $file_path,
				'post_mime_type' => mime_content_type( $file_path ),
				'post_excerpt' => $filename,
				'post_content' => $filename,
				'size'     => filesize( $file_path ),
			);
			$attach_id = wp_insert_attachment( $artdata, $file_path , 0);
	
			//generate metadata and thumbnails
			if ($attach_data = wp_generate_attachment_metadata( $attach_id,$file_path)) {
				wp_update_attachment_metadata($attach_id, $attach_data);
			}
			array_push( $deactivate_media_array , $attach_id );
	}
	
}