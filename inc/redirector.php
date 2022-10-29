<?php
//root page redirect. JS redirect code is returned from our shortcode, and also have busy flash icon
//most pages redirect back here. we verify user is logged in and chose which page to redirect to based on that

// [redirector] is only on IMOK Logged In, IMOK Settings
//-If we are logged in
//	IMOK Setting page
//		stay
//	IMOK Logged In page
//		if no settings -> IMOK Settings page
//		if settings -> stay
//-If we are not logged in
//	IMOK Setting page
//		-> IMOK Logged In page
//	IMOK Logged In page
//		-> IMOK Logged In page

//wait image
//add_shortcode( 'imok_flash', 'imok_flash_func' );
//function imok_flash_func(){
//	return IMOK_PLUGIN_LOCATION_URL . '/images/flash.gif';
//}

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

add_shortcode( 'imok_redirector', 'imok_redirector_func' );
function imok_redirector_func(){
	$currentURL = get_permalink();
	$newURL = $currentURL; //assume we are already on the correct page. test this assumption below
	$page = get_page_by_title("IMOK Settings"); //then assume we are on IMOK Settings page
	$imokSettingsURL = get_permalink($page->ID);
	if( is_user_logged_in() ){
			if( $currentURL != $imokSettingsURL ){ //if not on IMOK-Settings see if we should be
				$user = wp_get_current_user();
				if( get_user_meta( $user->ID, 'imok_contact_email_1', true ) == true ) { //we have set up our settings already
					$page = get_page_by_title("IMOK Logged In");
					$newURL = get_permalink($page->ID);
				}
				else{ //we need to set up our settings. 1st login?
					$page = get_page_by_title("IMOK Settings");
					$newURL = get_permalink($page->ID);
				}
			}
		}
		else{
			$page = get_page_by_title("IMOK Log In");
			$newURL = get_permalink($page->ID);
		}
	if($currentURL != $newURL){//only redirect if we are changing pages. compare current URL with redirected one so we don't loop
		$flash = IMOK_PLUGIN_LOCATION_URL . '/images/flash.gif';
		$string = "<!-- wp:image {'align':'center','sizeSlug':'large'} -->
<figure class='wp-block-image aligncenter size-large'><img src='$flash' alt=''/><figcaption>Please Wait...</figcaption></figure>
<!-- /wp:image -->

<script>window.location.replace('$newURL');</script>
		";
		return( $string );
	}
}

?>
