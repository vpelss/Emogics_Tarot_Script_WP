<?php
//set login form shortcode and logout URL shortcodes with our redirects

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//login form shortcode
add_shortcode( 'wp_login_form', 'imok_login_form_func' );
function imok_login_form_func(){
	$page = get_page_by_title("IMOK Logged In");
	$homeURL = get_permalink($page->ID);
	$wp_login_form = wp_login_form(
		['echo' => false,	//'redirect' => $site_url,
		'redirect' => $homeURL,
        'form_id' => 'loginform',
        'label_username' => __( 'Login Email' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'remember' => true,
		'value_remember' => true
		]);

		$imok_root_url = IMOK_ROOT_URL;
		$wp_login_form =  $wp_login_form . "<p id='nav'>
				<a href='$imok_root_url/wp-login.php?action=register'>Register</a> |	<a href='$imok_root_url/wp-login.php?action=lostpassword'>Lost your password?</a>
			</p>";

	return $wp_login_form;

	};

//need this for registration login, which likely comes from wp-login.php not our custom form
add_filter( 'login_redirect', 'imok_login_redirect' );
function imok_login_redirect() {
    $page = get_page_by_title("IMOK Logged In");
	$homeURL = get_permalink($page->ID);
    return $homeURL ;
}

//create a wp logout url and send to shortcode : wp_logout_url( string $redirect = '' ) : redirect to main page on log out
add_shortcode( 'wp_logout_url', 'imok_logout_url_func' );
function imok_logout_url_func(){
		$page = get_page_by_title("IMOK Log In");
		$homeURL = get_permalink($page->ID);
		return wp_logout_url( $homeURL );
	}

add_action( 'login_enqueue_scripts', 'imok_my_login_logo' );
function imok_my_login_logo() {
	echo "
	    <style type='text/css'>
        #login h1 a, .login h1 a {
            background-image: url( IMOK_PLUGIN_LOCATION_URL . '/images/imok-logo.svg');
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
    </style>
	";
	}


/*
    <style type='text/css'>
        #login h1 a, .login h1 a {
            background-image: url( IMOK_PLUGIN_LOCATION_URL . '/images/imok-logo.svg');
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
    </style>

 */
?>
