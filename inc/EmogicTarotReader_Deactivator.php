<?php

//this will only delete pages that were set up by the plugin
//new unique pages will be left in wordpress, but will lose their parent pages

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class EmogicTarotReader_Deactivator{

	public static function deactivate(){
						
		//remove created pages
		$pages = get_option( EMOGIC_TAROT_PLUGIN_PAGES_ARRAY_OPTION );
		if($pages != false){
			foreach ($pages as $page_name) {
				$page = get_page_by_path($page_name);
				wp_delete_post($page->ID , 1);
				}
		}
		//remove images
		$images_ids = get_option( EMOGIC_TAROT_PLUGIN_MEDIA_ARRAY_OPTION );
		if($images_ids != false){
			foreach ($images_ids as $images_id) {
				$result = wp_delete_attachment( $images_id , true );
				}
		}
		//delete copied images
		if( WP_Filesystem() ){
			global $wp_filesystem;
			$image_folder = get_home_path() . "wp-content/uploads/" .EMOGIC_TAROT_PLUGIN_MEDIA_FOLDER . "/";
			$wp_filesystem->rmdir($image_folder, true);
		}
		
		 //no longer needed
		delete_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_OPTION ); 
		delete_option( EMOGIC_TAROT_PLUGIN_FROM_EMAIL_DISPLAY_OPTION ); 
		delete_option( EMOGIC_TAROT_PLUGIN_PAGES_ARRAY_OPTION ); 
		delete_option( EMOGIC_TAROT_PLUGIN_MEDIA_ARRAY_OPTION ); 
		delete_option( EMOGIC_TAROT_PLUGIN_VERSION_OPTION ); 
		delete_option( EMOGIC_TAROT_PLUGIN_EMAIL_SUBJECT_OPTION ); 

		flush_rewrite_rules();
	}

}