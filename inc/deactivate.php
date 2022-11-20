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

		//remove all user metadata starting with imok
		//$users = get_users();
/*
		foreach ( $users as $user ) {
			delete_user_meta($user->ID , 'EMOGIC_TAROT_timezone');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_contact_email_1');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_contact_email_2');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_contact_email_3');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_email_form');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_alert_date');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_alert_time');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_alert_interval');
			delete_user_meta($user->ID , 'EMOGIC_TAROT_pre_warn_time');
			}
*/
		flush_rewrite_rules();
	}

}

//register_deactivation_hook( EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME , array( 'deactivate' , 'deactivate_plugin') );

