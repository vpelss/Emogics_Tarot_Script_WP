<?php

if ( ! defined( 'ABSPATH' ) ) {	exit($staus='ABSPATH not defn'); } //exit if directly accessed

//set a wp_cron interval
add_filter( 'cron_schedules', 'imok_add_cron_interval' );
function imok_add_cron_interval( $schedules ) {
    $schedules['fifteen_minutes'] = array(
        'interval' => 900,
        'display'  => esc_html__( 'Every Fifteen Minutes' ), );
    return $schedules;
}

//create my hook
add_action( 'imok_cron_hook', 'imok_cron_exec' );

//scheduled our cron
if ( ! wp_next_scheduled( 'imok_cron_hook' ) ) {
    wp_schedule_event( time(), 'fifteen_minutes', 'imok_cron_hook' );
}

function imok_cron_exec(){
//get an array of our users
$users = get_users();
$msg = '';
$msg1 = '';

foreach ( $users as $user ) {
    $userID = $user->ID;
    $imok_contact_email_1 = get_user_meta( $userID , 'imok_contact_email_1', true ); // imok_contact_email_1
    if( ! get_user_meta( $user->ID , 'imok_timezone', true ) ){ continue; }
    $imok_timezone = 60 * get_user_meta( $user->ID , 'imok_timezone', true ); //in minutes * 60

    if( is_email($imok_contact_email_1) and $imok_timezone){ //did we save settings
       	$now_UTC = current_time("timestamp" , 1); //now in UTC time

        $imok_alert_date = get_user_meta( $userID, 'imok_alert_date', true );
        $imok_alert_time = get_user_meta( $userID, 'imok_alert_time', true );

        $imok_alert_date_time_string_local = $imok_alert_date . ' ' . $imok_alert_time;
        $imok_alert_unix_time =  strtotime( $imok_alert_date_time_string_local ) + $imok_timezone; //converts time (ignoring timezone) , need to add users timezone so we can convert to GMT to compare

        $message;
        $result;
        apply_filters( 'wp_mail_content_type',  "text/html" );
        if($imok_alert_unix_time <= $now_UTC){#alarm was/is triggered , email to list
            $email_from = 'From: imok <imok@emogic.com>';
            $email_to = array();
            array_push( $email_to , get_user_meta( $userID , 'imok_contact_email_1', true ) );
            array_push( $email_to , get_user_meta( $userID , 'imok_contact_email_2', true ) );
            array_push( $email_to , get_user_meta( $userID , 'imok_contact_email_3', true ) );
            array_push( $email_to , $user->user_email );
            $subject = "IMOK alert";
            $message = get_user_meta( $userID , 'imok_email_form', true );
            $headers = $email_from;
            $result = wp_mail( $email_to , $subject , $message , $headers  );
            }
        elseif( $now_UTC > ($imok_alert_unix_time - (3600 * get_user_meta( $userID , 'imok_pre_warn_time', true )) ) ){ //pre-alert time , email to client
            $email_from = 'From: imok <imok@emogic.com>';
            $email_to = $user->user_email;
            $subject = "IMOK pre-alert";
            $message = "Your IMOK Alert will be triggered and sent to your contact list at $imok_alert_date_time_string_local. Stop it by pushing IMOK button at " . IMOK_ROOT_URL;
            $headers = $email_from;
            $result = wp_mail( $email_to , $subject , $message , $headers  );
            }
        apply_filters( 'wp_mail_content_type',  "text/plain" );

        $imok_alert_unix_time_string = date("Y-m-d H:i"  , $imok_alert_unix_time); //convert to string
        $now_UTC_string = date("Y-m-d H:i"  , $now_UTC); //convert to string

        $msg1 = "user_id : {$user->ID} <br>
        mail result: {$result} <br>
        $message <br>
        imok_alert_unix_time_string : {$imok_alert_unix_time_string}<br>
		now_UTC_string : $now_UTC_string <br>";

        $msg = $msg . $msg1;
        }
    }

    return $msg;

}

?>
