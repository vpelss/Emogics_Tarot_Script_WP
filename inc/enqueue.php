<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

class enqueue{

	static function initialize(){
		$src = EMOGIC_TAROT_PLUGIN_LOCATION_URL . '/assets/imok.js';
		wp_enqueue_script('EMTSWP_JS' , $src );
	}

}

enqueue::initialize();
