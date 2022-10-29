<?php
//commands for the IMOK Logged In page and countdown function and message text for that page

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//shortcode for settings url. used with settings button on the imok-logged-in page. in case we change the imok-settings page name
add_shortcode( 'imok_settings_url', 'imok_settings_url_func' );
function imok_settings_url_func(){
	$page = get_page_by_title("IMOK Settings");
	$newURL = get_permalink($page->ID);
	return( $newURL );
}

//shortcode log_out_everywhere_else link
add_shortcode( 'imok_log_out_everywhere_else_url', 'imok_log_out_everywhere_else_url_func' );
function imok_log_out_everywhere_else_url_func(){
	$page = get_page_by_title("IMOK Logged In");
	$newURL = get_permalink($page->ID);
	$newURL = $newURL . "?command=log_out_everywhere_else";
	return( $newURL );
}

add_shortcode( 'imok_commands', 'imok_commands_func' );
function imok_commands_func(){
	$user = wp_get_current_user();

	if ($_SERVER["REQUEST_METHOD"] == "POST"){ $response = $_POST['command']; }
	else{ $response = $_GET['command'];	}

	if($response == 'imok'){
		return imok();
	}
	elseif($response == 'imnotok'){
		return imnotok();
	}
	elseif($response == 'imokcron'){
		//return imok_cron_exec();
	}
	elseif($response == 'log_out_everywhere_else'){
		$user = wp_get_current_user();
		$sessions = WP_Session_Tokens::get_instance( $user->ID );
		$sessions->destroy_others(  wp_get_session_token() );
		$msg = 'You have logged out everywhere else.</br></br>';
		$msg = $msg . imok_countdown();
		return $msg;
	}
	//elseif($response == 'settings'){//may not use. allows us to rename stiing page slug and not need to update href as we use ?command=settings
		//$page = get_page_by_title("IMOK Settings");
		//$newURL = get_permalink($page->ID);
		//return( "<script>window.location.replace('$newURL');</script>" );
	//}
	elseif(1){//no command
		return imok_countdown();
	}

	}

function imnotok(){
	$user = wp_get_current_user();

	$email_from = 'From: imok <imok@emogic.com>';
	$email_to = array();
	array_push($email_to , get_user_meta( $user->ID, 'imok_contact_email_1', true ) );
	array_push($email_to , get_user_meta( $user->ID, 'imok_contact_email_2', true ) );
	array_push($email_to , get_user_meta( $user->ID, 'imok_contact_email_3', true ) );
	array_push( $email_to , $user->user_email );
	$email_to_str = implode( " : ", $email_to );

	$subject = "IM Not OK";
	$message = $user->display_name . ' ' . $user->user_email . " pushed the IM Not OK button. Please check on them.";
	$message = $message . "\r\n\r\n" . get_user_meta( $user->ID , 'imok_email_form', true );
	$headers = $email_from;
	$result = wp_mail( $email_to , $subject , $message , $headers  );
	return "IM Not OK Alert sent to your contact list:<br> {$email_to_str} <br><br>The following was sent to your contact list:<br>{$message}";
	}

function imok(){
	$user = wp_get_current_user();
	$unix_day = 60 * 60 * 24; //seconds in a day

	//get current unix time
	$now = current_time("timestamp" , 0); //in unix time no UTC

	//get users data
	$imok_alert_interval_unix_time = $unix_day * get_user_meta( $user->ID, 'imok_alert_interval', true );
	$imok_alert_date =  get_user_meta( $user->ID, 'imok_alert_date', true );
	$imok_alert_time = get_user_meta( $user->ID, 'imok_alert_time', true );
	//convert user settings to unix time
	$imok_alert_date_time_string = $imok_alert_date . ' ' . $imok_alert_time;
	$imok_alert_unix_time = strtotime( $imok_alert_date_time_string ); //convert to unix time

	//compare and reset alert time
	if( ($imok_alert_unix_time - $imok_alert_interval_unix_time) > $now  ){# we are a full alert interval before the alert date time. do nothing
		//return 1;
		}#do nothing
	if($imok_alert_unix_time <= $now){#alarm was/is triggered
		while( $imok_alert_unix_time <= $now ){
			$imok_alert_unix_time = $imok_alert_unix_time + $imok_alert_interval_unix_time;
		};
		//$msg = "You had not responded by the Alert time. An alert was likely sent out. Please let your contacts know you are all right.";
	}
	elseif( ($imok_alert_unix_time - $imok_alert_interval_unix_time) <= $now ){# we are clicking just before alarm will trigger in the window of the alert interval
		$imok_alert_unix_time = $imok_alert_unix_time + $imok_alert_interval_unix_time; //one ping please
	}

	//set in db
	$imok_alert_date = date("Y-m-d"  , $imok_alert_unix_time); //convert to string
	update_user_meta( $user->ID , 'imok_alert_date' , $imok_alert_date ) ;
	$imok_alert_time = date("H:i" , $imok_alert_unix_time); //convert to string
	update_user_meta( $user->ID , 'imok_alert_time' , $imok_alert_time ) ;

	//Send email to ? user

	//return and display message
	$now_str = date( "Y-m-d H:i", $now);
	$new_alert_date_time = date( $imok_alert_date . " " . $imok_alert_time , $imok_alert_unix_time);
	$msg = imok_countdown();
	//$msg2 = "<br>Start alert time: {$imok_alert_date_time_string}<br>Now: {$now_str}<br>New alert time: {$new_alert_date_time}";
	return $msg;
}

add_shortcode( 'imok_countdown', 'imok_countdown' );
function imok_countdown(){
	$user = wp_get_current_user();
	//$unix_day = 60 * 60 * 24; //seconds in a day

	//NOTE: Server and user PC are in different time zones so:
	//when comparing on server convert all user pc times and server times to UTC
	//JS routines see unix timestamp as UTC but shows as local
	//so on user PC (JS) convert all times to UTC

	//$server_timezone_diff = current_time("timestamp" , 0) - current_time("timestamp" , 1);

	$now_UTC = current_time("timestamp" , 1); //now in UTC time

	//alert time local to user PC
	$imok_alert_date =  get_user_meta( $user->ID, 'imok_alert_date', true );
	$imok_alert_time = get_user_meta( $user->ID, 'imok_alert_time', true );
	//$imok_timezone = get_user_meta( $user->ID , 'imok_timezone', true );
	if( get_user_meta( $user->ID , 'imok_timezone', true ) ) {
		$imok_timezone = 60 * get_user_meta( $user->ID , 'imok_timezone', true );
		} //in minutes * 60

	$imok_alert_date_time_string_local = $imok_alert_date . ' ' . $imok_alert_time;
	if( $imok_alert_date_time_string_local ){
		$imok_alert_unix_time =  strtotime( $imok_alert_date_time_string_local ) + $imok_timezone; //converts time (ignoring timezone) , need to add users timezone so we can convert to GMT to compare
	}

	$imok_alert_unix_time_string = date("Y-m-d H:i"  , $imok_alert_unix_time); //convert to string
	$now_UTC_string = date("Y-m-d H:i"  , $now_UTC); //convert to string

	if($imok_alert_unix_time <= $now_UTC){#alarm was/is triggered
		$msg = "You had not responded by the Alert time. An alert was likely sent out. Please let your contacts know you are all right.";
		}
	else{
		$msg = "Push 'IM OK' before:<br>
		<font color='red'>{$imok_alert_date_time_string_local}</font><br>
		<font id='countdown'>countdown</font><br>
		<font id='timezone_error' color='orange'></font>

		<script>
		var trigger_time = $imok_alert_unix_time;
		function countdown() {
			var now = Date.now() / 1000; //in seconds
			var difference_seconds = trigger_time - now;
			if(difference_seconds <= 0){
				document.getElementById('countdown').innerHTML = 'Timeout exceeded. Alerts are being sent.';
				}
			else{
				var days = Math.floor( difference_seconds / (60 * 60 * 24) );
				var hours = Math.floor( (difference_seconds / (60 * 60)) % 24);
				var minutes = Math.floor( (difference_seconds / (60)) % 60 );
				var seconds = Math.floor( difference_seconds % 60 );

				var countdown_string = '' + days + ' days ' + hours + ' hours ' + minutes + ' minutes ' + seconds + ' seconds';
				document.getElementById('countdown').innerHTML = countdown_string;
				}
		}
		setInterval( countdown , 5000 * 1 ); //update every 15 seconds
		countdown(); //run now

		//test for wrong timezone by using js and comparing with stored timezone
		const d = new Date();
		let timezone= d.getTimezoneOffset();
		if((timezone * 60) != $imok_timezone){
			document.getElementById('timezone_error').innerHTML	= `Your device timezone differs from your timezone on the server.
			To update, go to settings and click save.`;
		}
		</script>
		";
		}
	return $msg;
}

?>
