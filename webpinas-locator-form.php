<?php
/*
Plugin Name: Webpinas Locator Booking Form
Version: 1.0
Description: Webpinas Locator Booking Form
Author: Webpinas
Author URI: http://www.webpinas.com.ph/
*/


//name, phone, email address
//This functions install the necessaey database for this form

function webpinas_google_location_form_install() {
            global $wpdb;
    
            $table_name = $wpdb->prefix . 'google_location_form';
    
            $charset_collate = $wpdb->get_charset_collate();
    
            $sql = "CREATE TABLE $table_name (
                        id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        venue_address varchar(500) DEFAULT NULL,
			suburb varchar(200) DEFAULT NULL,
                        post_code varchar(250) DEFAULT NULL,
                        booking_date DATE DEFAULT NULL,
                        contact_name varchar(250) DEFAULT NULL,
                        contact_email varchar(250) DEFAULT NULL,
                        contact_phone varchar(250) DEFAULT NULL,
                        IP_Address varchar(500) DEFAULT NULL,
                        form_dump longtext DEFAULT NULL,
                        date_added DATE DEFAULT NULL
                ) $charset_collate;";
    
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
            
    }
    
    register_activation_hook(__FILE__, 'webpinas_google_location_form_install');
    
    //functions file for plugin
    include(plugin_dir_path(__FILE__) . 'functions.php');

 function webpinas_glf_wp_enqueue_script(){
$options                    = get_option('webpinas_glf_fields_settings');
if($options['google_api_key']) {$key = "key={$options['google_api_key']}&";}
//jQuery UI date picker file
wp_enqueue_script( 'jquery-ui' );	
wp_enqueue_style('e2b-admin-ui-css','http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css',false,"1.9.0",false);
wp_enqueue_script( 'pcfe-google-places-api', "http://maps.googleapis.com/maps/api/js?{$key}libraries=places" );
//wp_enqueue_script('webpinas_google_location_form_script', plugin_dir_url(__FILE__) . '/script.js', array());
}
//function webpinas_glf_wp_enqueue_style(){
//
//}
add_action('wp_enqueue_scripts', 'webpinas_glf_wp_enqueue_script'); 
//add_action('wp_enqueue_styles', 'webpinas_glf_wp_enqueue_style'); 
    // To prevent redirect errors 
add_action('init', 'webpinas_do_output_buffer');

function webpinas_do_output_buffer() {
    ob_start();
}    
/**
* This function execute the plugin by shortcode
*/
function webpinas_google_location_form() {
     webpinas_location_form_html();
}

add_shortcode('webpinas-location-form', 'webpinas_google_location_form');