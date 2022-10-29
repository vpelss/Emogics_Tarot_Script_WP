<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit($staus='WP_UNINSTALL_PLUGIN not defn');
	//die('WP_UNINSTALL_PLUGIN not defn');
}

/*
		$books = get_posts( array('book_type' => 'book' , 'numberposts' => -1) );
		foreach($books as $book){
			wp_delete_post( $book->ID , true );
		}
*/

		global $wpdb;
		$wpdb->query("DELETE FROM wp_posts WHERE post_type = 'imok_posts'");
		$wpdb->query("DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT ID FROM wp_posts)");
		$wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT ID FROM wp_posts)");
