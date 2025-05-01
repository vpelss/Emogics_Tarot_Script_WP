<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//read all files, folders in /pages
//you can have multiple folder levels, but not recommended. eg: /pages/databases/runes/(runedb)
//create pages for all files and folders following directory structure
//page name will be same as folder or file name
//only root folder files will be published. the databases are in draft as some may not want their databases exposed

class EmogicTarotReader_Activator{
	
	public static function activate(){
		
		$upload_array = wp_upload_dir();
		DEFINE( 'EMOGIC_TAROT_PLUGIN_UPLOADS_PATH' , $upload_array['basedir'] );
		
		add_option(EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION , 'Tarot Reading');
		
		self::read_and_create_pages();
		
		//$pth = 'wp-content/plugins/' . EMOGIC_TAROT_PLUGIN_DIR_BASENAME . '/images';
		//define( 'UPLOADS',  $pth  );
		
		//https://wordpress.stackexchange.com/questions/222540/change-wordpress-upload-path-and-url
		//add_filter( 'pre_option_upload_path', ["EmogicTarotReader_Activator" , "upload_path"] );
		//add_filter( 'pre_option_upload_url_path', ["EmogicTarotReader_Activator" , "upload_url_path"] );

		//disable thumbnails : https://perishablepress.com/disable-wordpress-generated-images/
		add_action('intermediate_image_sizes_advanced', ['EmogicTarotReader_Activator' , 'shapeSpace_disable_image_sizes'] );
		add_action('init', ['EmogicTarotReader_Activator' , 'shapeSpace_disable_other_image_sizes']);
		add_filter('big_image_size_threshold', '__return_false'); // disable scaled image size

			if( ! ( defined( 'WP_HOME' ) and str_contains(WP_HOME , 'playground.wordpress.net') )  ){//ignore for sake of wp playground. It does not like copy functions, etc
				self::images_to_media_library();
		/*try{
			self::images_to_media_library();
		}
		catch(Exception $e){
			//ignore for sake of wp playground. It does not like copy functions, etc
		}*/
			}
		
		//remove_filter( 'pre_option_upload_path', self::upload_path );
		//remove_filter( 'pre_option_upload_path', self::upload_url_path );
		
		flush_rewrite_rules();
	}
	
	// disable generated image sizes
	public static function shapeSpace_disable_image_sizes($sizes) {	
		unset($sizes['thumbnail']);    // disable thumbnail size
		unset($sizes['medium']);       // disable medium size
		unset($sizes['large']);        // disable large size
		unset($sizes['medium_large']); // disable medium-large size
		unset($sizes['1536x1536']);    // disable 2x medium-large size
		unset($sizes['2048x2048']);    // disable 2x large size
		return $sizes;
	}

	// disable other image sizes
	public static function shapeSpace_disable_other_image_sizes() {	
		remove_image_size('post-thumbnail'); // disable images added via set_post_thumbnail_size() 
		remove_image_size('another-size');   // disable any other added image sizes
	}

	//https://developer.wordpress.org/reference/functions/wp_upload_dir/
	/*
	Checks the ‘upload_path’ option, which should be from the web root folder, and if it isn’t empty it will be used.
	If it is empty, then the path will be ‘WP_CONTENT_DIR/uploads’.
	If the ‘UPLOADS’ constant is defined, then it will override the ‘upload_path’ option and ‘WP_CONTENT_DIR/uploads’ path.

	The upload URL path is set either by the ‘upload_url_path’ option or by using the ‘WP_CONTENT_URL’ constant and appending ‘/uploads’ to the path.
	
	If the ‘uploads_use_yearmonth_folders’ is set to true (checkbox if checked in the administration settings panel), then the time will be used. The format will be year first and then month.
	
	If the path couldn’t be created, then an error will be returned with the key ‘error’ containing the error message. The error suggests that the parent directory is not writable by the server.
	*/
	
	public static function upload_path(){
		$pth = 'wp-content/plugins/' . EMOGIC_TAROT_PLUGIN_DIR_BASENAME . '/images';
		return $pth;
	}
	
	public static function upload_url_path(){
		$pth = EMOGIC_TAROT_PLUGIN_WP_ROOT_URL . '/';
		return $pth ;
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
		//throw new Exception("test throw");
		$deactivate_media_array = array();		
		$from = EMOGIC_TAROT_PLUGIN_PATH . 'images/'; 
		$to = EMOGIC_TAROT_PLUGIN_UPLOADS_PATH . '/' . EMOGIC_TAROT_PLUGIN_MEDIA_FOLDER ;
		$sub_path = '';
		
		self::recursive_copy($from , $to , $sub_path , $deactivate_media_array); //copy files		
		add_option( EMOGIC_TAROT_PLUGIN_MEDIA_ARRAY_OPTION , array_reverse($deactivate_media_array));	
	}
	
	public static function recursive_copy($src , $dst , $sub_path , &$deactivate_media_array) { //$src and $dst do not have lagging slashes	
		$dir = opendir($src);
		@mkdir($dst);		
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) {
				$src_path_and_file = $src . '/' . $file;
				$dst_path_and_file = $dst . '/' . $file;
				$sub_path_and_file = $sub_path . '/' . $file;
				if ( is_dir($src_path_and_file) ) {
					self::recursive_copy($src_path_and_file , $dst_path_and_file ,  $sub_path_and_file , $deactivate_media_array ); 
				} 
			if ( is_file($src_path_and_file) )  {
					@copy( $src_path_and_file , $dst_path_and_file);
					self::copy_image_to_media_library($dst_path_and_file , $file , $deactivate_media_array,  $sub_path_and_file );
				} 
			} 
		} 
		closedir($dir); 
	}

	public static function copy_image_to_media_library($path_and_file , $filename , &$deactivate_media_array , $subpath_and_file){
	
		$artdata = array(
				'post_author' => 1, 
				'post_title' => $filename, 
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $filename)),											'post_modified' => current_time('mysql'),
				'post_modified_gmt' => current_time('mysql'),
				'post_parent' => 0,
				'post_type' => 'attachment',
				//'guid' => $file_path,
				'post_mime_type' => 'image/jpg',
				'post_excerpt' => $filename,
				'post_content' => $filename,
				'size'     => filesize( $path_and_file ),
			);
			
			$target_subpath_and_file = EMOGIC_TAROT_PLUGIN_MEDIA_FOLDER . $subpath_and_file;
			$attach_id = wp_insert_attachment( $artdata , $target_subpath_and_file , 0);
	
			//generate metadata and thumbnails
			if ($attach_data = wp_generate_attachment_metadata( $attach_id , $path_and_file)) {
				wp_update_attachment_metadata($attach_id , $attach_data);
			}
			array_push( $deactivate_media_array , $attach_id );

	}
	
}