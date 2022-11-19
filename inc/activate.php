<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class activate{

	public function activate_plugin(){
		//imok_read_and_create_pages(); //create default pages
		//flush_rewrite_rules();
	}

}

register_activation_hook( EMOGIC_TAROT_PLUGIN_PATH_AND_FILENAME , array( 'activate' , 'activate_plugin') );
?>
