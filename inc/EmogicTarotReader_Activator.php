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
		self::copy_images_to_media_library();
		flush_rewrite_rules();
	}

	public static function read_and_create_pages(){
	$dir = EMOGIC_TAROT_PLUGIN_PATH . "/pages";
	$parent_id = 0;
	$page_path_parent = '';

	$deactivate_file_array = array(); //to use on deactivate so we can delete files plugin added
	self::add_pages($dir,$parent_id,$page_path_parent,$deactivate_file_array);//$pages is by reference
	//stored in wp db
	add_option('EmogicTarotReader_option_deactivate_file_array' , array_reverse($deactivate_file_array));
	}

	public static function add_pages($dir,$parent_id,$page_path_parent,&$deactivate_file_array){
	//note: recursive routine, do not change arg values here!!!
	$files = array_diff(scandir($dir), array('..', '.'));
	foreach ($files as $file) {
		//does file exist?
		$page_path = $page_path_parent.'/'.$file;
		$wp_post = get_page_by_path($page_path); //returns post object or null
		if(is_file($dir.'/'.$file)){//for files
			$post_status = 'draft';
			if(! $parent_id)  //only publish if root pages
				$post_status = 'publish';
			if( str_starts_with($page_path , '/' . EMOGIC_TAROT_PLUGIN_READING_FOLDER ) ) //oh, and files under EMOGIC_TAROT_PLUGIN_READING_FOLDER too
				$post_status = 'publish'; 
			if(! isset($wp_post)) //file does not exist
				$parent_id_temp =  self::post_page_if_required( $parent_id , $page_path_parent , $file , $dir , $post_status);
			array_push( $deactivate_file_array , $page_path_parent.'/'.$file );
		}
		if(is_dir($dir.'/'.$file)){ //for directories create empty page
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
	
	public static function copy_images_to_media_library(){
		$from = EMOGIC_TAROT_PLUGIN_PATH . "/images/";
		$to = get_home_path() . "wp-content/uploads/Emogic-Images";
		$result = mkdir($to, 0755);
		
		//recursive_copy2($from,$to);
		
		$t = 9;
		
	}
	
	public static function fetch_media($file_url, $post_id) {
		require_once(ABSPATH . 'wp-load.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		global $wpdb;
	
		if(!$post_id) {
			return false;
		}
	
		//directory to import to	
		$artDir = 'wp-content/uploads/importedmedia/';
	
		//if the directory doesn't exist, create it	
		if(!file_exists(ABSPATH.$artDir)) {
			mkdir(ABSPATH.$artDir);
		}
	
		//rename the file... alternatively, you could explode on "/" and keep the original file name
		$ext = array_pop(explode(".", $file_url));
		$new_filename = 'blogmedia-'.$post_id.".".$ext; //if your post has multiple files, you may need to add a random number to the file name to prevent overwrites
	
		if (@fclose(@fopen($file_url, "r"))) { //make sure the file actually exists
			copy($file_url, ABSPATH.$artDir.$new_filename);
	
			$siteurl = get_option('siteurl');
			$file_info = getimagesize(ABSPATH.$artDir.$new_filename);
	
			//create an array of attachment data to insert into wp_posts table
			$artdata = array();
			$artdata = array(
				'post_author' => 1, 
				'post_date' => current_time('mysql'),
				'post_date_gmt' => current_time('mysql'),
				'post_title' => $new_filename, 
				'post_status' => 'inherit',
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $new_filename)),											'post_modified' => current_time('mysql'),
				'post_modified_gmt' => current_time('mysql'),
				'post_parent' => $post_id,
				'post_type' => 'attachment',
				'guid' => $siteurl.'/'.$artDir.$new_filename,
				'post_mime_type' => $file_info['mime'],
				'post_excerpt' => '',
				'post_content' => ''
			);
	
			$uploads = wp_upload_dir();
			$save_path = $uploads['basedir'].'/importedmedia/'.$new_filename;
	
			//insert the database record
			$attach_id = wp_insert_attachment( $artdata, $save_path, $post_id );
	
			//generate metadata and thumbnails
			if ($attach_data = wp_generate_attachment_metadata( $attach_id, $save_path)) {
				wp_update_attachment_metadata($attach_id, $attach_data);
			}
	
			//optional make it the featured image of the post it's attached to
			$rows_affected = $wpdb->insert($wpdb->prefix.'postmeta', array('post_id' => $post_id, 'meta_key' => '_thumbnail_id', 'meta_value' => $attach_id));
		}
		else {
			return false;
		}
		return true;
	}
	
	
	public static function recursive_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recursive_copy($src .'/'. $file, $dst .'/'. $file);
				}
				else {
					copy($src .'/'. $file,$dst .'/'. $file);
				}
			}
		}
		closedir($dir);
	}

	public static function recursive_copy2($source,$dest) {	
		//$source = "dir/dir/dir";
		//$dest= "dest/dir";
		
		mkdir($dest, 0755);
		foreach (
		 $iterator = new \RecursiveIteratorIterator(
		  new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
		  \RecursiveIteratorIterator::SELF_FIRST) as $item
		) {
		  if ($item->isDir()) {
			mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
		  } else {
			copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
		  }
		}
	}

}
