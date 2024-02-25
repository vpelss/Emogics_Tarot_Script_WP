<?php

//this will only delete pages that were set up by the plugin
//new unique pages will be left in wordpress, but will loose their parent pages

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class EmogicTarotReader_Deactivator{

/*
add_action("template_redirect", "warn_onDeactivate");
function warn_onDeactivate(){
	if( is_admin() ){
			}
 }
 */

	public static function deactivate(){
		delete_option('ETSWP_from_email_field'); //no longer needed
		delete_option('ETSWP_email_display_name_field'); //no longer needed
		//remove created pages
		$pages = get_option('EmogicTarotReader_option_deactivate_file_array');
		delete_option('EmogicTarotReader_option_deactivate_file_array'); //no longer needed	
		foreach ($pages as $page_name) {
			$page = get_page_by_path($page_name);
			wp_delete_post($page->ID , 1);
			}
			
		//remove images
		$images_ids = get_option('EmogicTarotReader_option_deactivate_media_array');
		delete_option('EmogicTarotReader_option_deactivate_media_array'); //no longer needed
		foreach ($images_ids as $images_id) {
			$result = wp_delete_attachment( $images_id , true );
			$t = 9;
			}
			
		flush_rewrite_rules();
	}

}