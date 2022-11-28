<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class ETSWP_deactivator{

	public static function deactivate(){

		//remove created pages
		$pages = get_option('ETSWP_deactivate_file_array');
		delete_option('ETSWP_deactivate_file_array'); //no longer needed
		foreach ($pages as $page_name) {
			$page = get_page_by_path($page_name);
			wp_delete_post($page->ID , 1);
			}

		flush_rewrite_rules();
	}

}
