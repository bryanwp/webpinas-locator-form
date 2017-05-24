<?php

/**
 * This file sets admin menus and functions.
 */
defined( 'ABSPATH' ) or die( 'You don\'t have access to this page.');


function webpinas_glf_insert_db($data) {
   // var_dump($data);
    if (    isset($data) 
            and ! empty($data['venue_address']) 
            and ! empty($data['booking_date']) 
    ) {
        
        global $wpdb;

$newDate = date("Y-m-d", strtotime($data['booking_date']));
        $db_result = $wpdb->insert(
                $wpdb->prefix . 'google_location_form', array(
                    
            'venue_address' => $data['venue_address'],
            'suburb' => $data['suburb'],
            'post_code' => $data['post_code'],       
            'booking_date' => $newDate,
            'contact_name' => $data['contact_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'],
            'IP_Address' => get_client_ip(),
            'form_dump' => $data['form_dump'],
            'date_added' => date("Y-m-d"),
                ), array(
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'       
                )
        );
        if ($db_result == true) {
            //send email
            webpinas_glf_send_notification_email($wpdb->insert_id, $data);
            //unset the form

$link['addressLine']=trim($data['venue_address']); 
$link['Suburb']=$data['suburb'];
$link['Postcode']=$data['post_code'];
$link['PartyDate'] = str_replace("-", "", $newDate);
$link['fullName']=$data['contact_name'];
$link['email']=$data['contact_email'];
 
            $options = get_option('webpinas_glf_fields_settings');
            wp_redirect($options['redirect_page']."?".http_build_query($link, null, '&', PHP_QUERY_RFC3986));
            exit;
        }
        elseif ($db_result == FALSE) {
        wp_die( 'ERROR: Database ERROR occured on this website. Contact the site admin to report this.');
    }
    }
    else {wp_die( 'ERROR: You did not select the venue_address and booking date.');}
}

function webpinas_glf_send_notification_email($id, $data){
    
     $options                    = get_option('webpinas_glf_email_settings');
     
    $email_notification_enabled = $options['webpinas_glf_email_notification'];
    $to = $options['webpinas_glf_email_add'];
    $message_with_sc = $options['webpinas_glf_email_template'];
    $message = webpinas_glf_email_sc($message_with_sc, $data);
    
$to_email = explode (',', $to);
$x=0;
foreach ($to_email as $emails) {
            if ($x==0) {$headers[$x]= "To: <$emails>\r\n"; }
            else {$headers[$x] = "CC: <$emails>\r\n"; }
            $x++;
}
    
    if ($email_notification_enabled==1 
            and !empty($message) 
            )   
        {
        add_filter( 'wp_mail_content_type', 'locatorform_set_html_mail_content_type' );
        $mail = wp_mail ($headers[0], "Booking Notification Email", $message, $headers);
        // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
        remove_filter( 'wp_mail_content_type', 'locatorform_set_html_mail_content_type' );
        return $mail;
        }
}
function locatorform_set_html_mail_content_type() {
    return 'text/html';
}
function webpinas_glf_email_sc($message_with_sc, $data){
    //search and replace array  
$search  = array(
    '[webpinas_venue_address]',
    '[webpinas_suburb]', 
    '[webpinas_post_code]', 
    '[webpinas_booking_date]', 
    '[webpinas_contact_name]', 
    '[webpinas_contact_email]',
    '[webpinas_contact_phone]',
    '[webpinas_ip_address]'
    );
$replace = array(
    $data['venue_address'], // value for [webpinas_venue_address]
    $data['suburb'], // value for [webpinas_suburb]
    $data['post_code'], // value for [webpinas_post_code]
    $data['booking_date'], // value for [webpinas_booking_date]
    $data['contact_name'], // value for [webpinas_contact_name]
    $data['contact_email'], // value for [webpinas_contact_email]
    $data['contact_phone'], // value for [webpinas_contact_phone]
    get_client_ip(), // value for [webpinas_ip_address]
    );
    return $replaced_message = str_replace ( $search, $replace , $message_with_sc );
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress .= $_SERVER['HTTP_CLIENT_IP'] . ' - ';}
        
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress .= $_SERVER['HTTP_X_FORWARDED_FOR']. ' - ';}
        
    if(isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress .= $_SERVER['HTTP_X_FORWARDED']. ' - ';}
        
    if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress .= $_SERVER['HTTP_FORWARDED_FOR']. ' - ';}
        
    if(isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress .= $_SERVER['HTTP_FORWARDED']. ' - ';}
        
    if(isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress .= $_SERVER['REMOTE_ADDR']. ' - ';}
        
    if($ipaddress == '') {
        $ipaddress = 'UNKNOWN';}
    return $ipaddress;
}

function validate_update_webpinas_location_form() {
     if (isset($_POST['action']) and $_POST['action']=="webpinas_glf_main_form") {unset($_POST['action']);
       $data['venue_address'] = filter_input(INPUT_POST, 'autocomplete');unset($_POST['autocomplete']);
       $data['suburb'] = filter_input(INPUT_POST, 'locality');unset($_POST['locality']);
       $data['post_code'] = filter_input(INPUT_POST, 'postal_code');unset($_POST['postal_code']);
       $data['booking_date'] = filter_input(INPUT_POST, 'datepicker');unset($_POST['datepicker']);
       $data['contact_name'] = filter_input(INPUT_POST, 'name_field'); unset($_POST['name_field']);
       $data['contact_email'] = filter_input(INPUT_POST, 'email_field');unset($_POST['email_field']);
       $data['contact_phone'] = filter_input(INPUT_POST, 'phone_field');unset($_POST['phone_field']);
       $data['form_dump'] = serialize($_POST);
       unset($_POST);
       webpinas_glf_insert_db($data);      
       
    }
}

function webpinas_location_form_html() {
    $options                    = get_option('webpinas_glf_fields_settings');
    if(isset($options['enable_captcha']) and $options['enable_captcha']==1){
        
        require_once __DIR__.'/src-recaptcha/autoload.php';
        $siteKey = $options['google_site_key'];
        $secret = $options['google_secret_key'];

        if ($siteKey === '' || $secret === '') {$error = 'Add your keys in the Webpinas Locator Booking Form settings page : If you do not have keys already then visit  https://www.google.com/recaptcha/admin';}
            
        if (isset($_POST['g-recaptcha-response']))  {
                  $recaptcha = new \ReCaptcha\ReCaptcha($secret);
                  $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
                  
                  if ($resp->isSuccess()) {
                      unset($_POST['g-recaptcha-response']);
                      validate_update_webpinas_location_form();
                  }
                  else {
                      $error = 'Google reCAPTCHA Error: ';
                      foreach ($resp->getErrorCodes() as $code) {
                      $error .= $code." ";
                      }
                  }
        }
}else {validate_update_webpinas_location_form();}
  global $count_google_form;
  $count_google_form+= 1;
   ?> 
<style>.pac-container.pac-logo{ z-index: 999999 !important; }</style>
<form action="" method="post" class="invalid" id="google_form_<?php echo $count_google_form;?>">
    <p id='g-error' style="color: red;" class="g-error"><?php if(isset($error)) {echo $error;} ?></p>
    <input type="hidden" name="action" value="webpinas_glf_main_form"/>
    <div class="panel1-formdata">
            <div class="gs-box"><input id="autocomplete<?php echo $count_google_form;?>" name="autocomplete" type="text" class="venue" onfocus="geolocate<?php echo $count_google_form;?>()" size="50" placeholder="Venue Address" autocomplete="off" required></div>

            <?php 
            $options                    = get_option('webpinas_glf_fields_settings');
             if(isset($options['name_field']) and $options['name_field']==1) { echo  "<div class='gs-box'><input id='name_field{$count_google_form}' name='name_field' class='field' type='text' placeholder='Name' "; if(isset($options['name_field_required'])and $options['name_field_required']==1){echo "required";} echo '></div>';}
             if(isset($options['email_field']) and $options['email_field']==1) { echo  "<div class='gs-box'><input id='email_field{$count_google_form}' name='email_field' class='field'  type='email' placeholder='Email' "; if(isset($options['email_field_required'])and $options['email_field_required']==1){echo "required";} echo '></div>';}
              if(isset($options['phone_field']) and $options['phone_field']==1) { echo  "<div class='gs-box'><input id='phone_field{$count_google_form}' name='phone_field' class='field' type='text' placeholder='Phone No' "; if(isset($options['phone_field_required'])and $options['phone_field_required']==1){echo "required";} echo '></div>';}
            $more_fields                   = get_option('webpinas_glf_add_more_fields');
            if ($more_fields) {
 foreach ($more_fields as $field) {
    if(isset($field['field_label']) and isset($field['field_name'])) {
     echo  '<div class="gs-box"><input id="'.$field['field_label'].'" name="'.$field['field_label'].'" class="field" type="text" placeholder="'.$field['field_name'].'" '; if(isset($field['field_required'])and $field['field_required']==1){echo "required";} echo '></div> ';
    }
            }}
            ?>





            <div class="gs-box locality"><input id="locality<?php echo $count_google_form;?>"  name="locality"  class="field" type="text" placeholder="SUBURB"></div>
            <div class="gs-box postal_code"><input id="postal_code<?php echo $count_google_form;?>"  name="postal_code"  class="field" type="text" placeholder="POST CODE"></div>
            <div class="gs-box datebox"><span class="preffered_date"><input data-role="date" data-inline="true" name="datepicker" autocomplete="off"  size="40"  id="datepicker_<?php echo $count_google_form;?>" class="datepicker_locator_form" aria-required="true" placeholder="Preferred Date" type="text" required> </span></div>

            <?php if(isset($siteKey)) {?><div class="gs-box"><div class="g-recaptcha" data-sitekey="<?php echo $siteKey ?>" ></div></div> <?php } ?>
            <div class="gs-button g-arrow"><input type="submit" value="<?php if(isset($options['submit_button']) and !empty($options['submit_button'])) { echo $options['submit_button'];} else {echo "Secure your booking";}?>" class="button bookonlinenow shake">
                <!--<img class="ajax-loader" src="http://127.0.0.1:208/wp-content/plugins/contact-form-7/images/ajax-loader.gif" alt="Sending ..." style="visibility: invisible ;">-->
            </div>
    
    </div>
    <div class="hidden-field">
        <input class="field" id="street_number" type="hidden" value=""><br><input class="field" id="route" type="hidden" value=""><br><input class="field" id="country" type="hidden" value="Australia"><br>
        <input class="field" id="administrative_area_level_1" type="hidden" value="NSW">
    </div>
    </form>

<script>
      var placeSearch, autocomplete<?php echo $count_google_form;?>;
      var componentForm<?php echo $count_google_form;?> = {
        //street_number: 'short_name',
        //route: 'long_name',
        locality: 'long_name',
        //administrative_area_level_1: 'short_name',
        //country: 'long_name',
        postal_code: 'short_name'    
      };

//window.onload=function initAutocomplete<?php echo $count_google_form;?>() {
       // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete<?php echo $count_google_form;?> = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete<?php echo $count_google_form;?>')),
            {types: ['geocode'], componentRestrictions: { country: 'au' }});

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
       google.maps.event.addListener(autocomplete<?php echo $count_google_form;?>, 'place_changed', function () {
         fillInAddress<?php echo $count_google_form;?>();
             });
//}
      function fillInAddress<?php echo $count_google_form;?>() {
        // Get the place details from the autocomplete object.
        var place = autocomplete<?php echo $count_google_form;?>.getPlace();

        for (var component in componentForm<?php echo $count_google_form;?>) {
          document.getElementById(component+'<?php echo $count_google_form;?>').value = '';
          document.getElementById(component+'<?php echo $count_google_form;?>').disabled = false;
        }

        // Get each component of the address from the place details
    // and fill the corresponding field on the form.

    var streetNumber = streetName = postcode = suburb = state = "";

    for (var i = 0; i < place.address_components.length; i++){
          var addressType = place.address_components[i].types[0];
          if (componentForm<?php echo $count_google_form;?>[addressType]) {
            var val = place.address_components[i][componentForm<?php echo $count_google_form;?>[addressType]];
            document.getElementById(addressType+'<?php echo $count_google_form;?>').value = val;
          } 
            
            if (addressType == "street_number"){ streetNumber = place.address_components[i]["short_name"];}
            else if (addressType == "route") { streetName = place.address_components[i]["long_name"]; }
            else if (addressType == "locality") { suburb = place.address_components[i]["long_name"];}
            else if (addressType == "postal_code") { postcode = place.address_components[i]["short_name"];}
            else if (addressType == "administrative_area_level_1"){ state = place.address_components[i]['short_name']; }
            
        }
           jQuery("#autocomplete<?php echo $count_google_form;?>").val(streetNumber + " " + streetName);
      }

      // Bias the autocomplete object to the user's geographical location,
      // as supplied by the browser's 'navigator.geolocation' object.
      function geolocate<?php echo $count_google_form;?>() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
            autocomplete<?php echo $count_google_form;?>.setBounds(circle.getBounds());
          });
        }
      }

jQuery(document).ready(function datefunction<?php echo $count_google_form;?>() {
jQuery('#datepicker_<?php echo $count_google_form;?>').datepicker({
    	dateFormat : 'dd-mm-yy',
 	showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
});
$('#datepicker_<?php echo $count_google_form;?>').click(function() {
  $('#datepicker_<?php echo $count_google_form;?>').blur();
});

$("#google_form_<?php echo $count_google_form;?>").submit(function(e) {
    var ref = $(this).find("[required]");
    $(ref).each(function(){
        if ( $(this).val() == '' )
        {
            alert("Required field should not be blank.");
            $(this).focus();
            e.preventDefault();
            return false;
        }
    });  return true;
});
});
					
</script>
    <?php
}


/***********************
 * Admin functions
 ***********************/

function webpinas_glf_custom_admin_menu() {
    add_menu_page('Webpinas Locator Form Settings', 'Webpinas Locator Form Settings', 'activate_plugins', 'admin_webpinas_glf', 'admin_webpinas_glf_form', 'dashicons-media-text');
   add_submenu_page('', 'Excel Download', 'Excel Download', 'activate_plugins', 'admin_webpinas_excel_download', 'admin_webpinas_excel_download');
    add_submenu_page('', 'CSV Download', 'CSV Download', 'activate_plugins', 'admin_webpinas_csv_download', 'admin_webpinas_csv_download');
   //webpinas_glf_view_data slug
    
}

add_action('admin_menu', 'webpinas_glf_custom_admin_menu');

function admin_webpinas_excel_download() {
    
        
       /** Error reporting */
//            error_reporting(E_ALL);
//            ini_set('display_errors', TRUE);
//            ini_set('display_startup_errors', TRUE);
           
        /** Include PHPExcel */
    
            require_once __DIR__.'/external_classes/Excel/Classes/PHPExcel.php';
            $objPHPExcel = new \PHPExcel();
        // var_dump($objPHPExcel);
            
            
        // Set document properties
            $objPHPExcel->getProperties()->setCreator("GymBus")
                                             ->setLastModifiedBy("GymBus")
                                             ->setTitle("GymBus Form Submission")
                                             ->setSubject("GymBus Form Submission")
                                             ->setDescription("This document contains the form submissions of your website.")
                                             ->setKeywords("GymBus")
                                             ->setCategory("GymBus");
        // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'ID')
                        ->setCellValue('B1', 'Venue Address')
                        ->setCellValue('C1', 'Suburb')
                        ->setCellValue('D1', 'Post Code')
                        ->setCellValue('E1', 'Booking Date')
                        ->setCellValue('F1', 'Contact Name')
                        ->setCellValue('G1', 'Contact email')
                        ->setCellValue('H1', 'Contact Phone')
                        ->setCellValue('I1', 'Street Address')
                        ->setCellValue('J1', 'IP Address')
                        ->setCellValue('K1', 'Date Added');
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);

        // query to get the resuts from the database
            global $wpdb;

            $tablename = $wpdb->prefix.'google_location_form';
            $rows = $wpdb->get_results( "SELECT * FROM $tablename" );    

            $i=2;
            foreach ($rows as $row) {

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue("A$i", $row->id)
                        ->setCellValue("B$i", $row->venue_address)
                        ->setCellValue("C$i", $row->suburb)
                        ->setCellValue("D$i", $row->post_code)
                        ->setCellValue("E$i", $row->booking_date)
                        ->setCellValue("F$i", $row->contact_name)
                        ->setCellValue("G$i", $row->contact_email)
                        ->setCellValue("H$i", $row->contact_phone)
                        ->setCellValue("I$i", $row->street_address)
                        ->setCellValue("J$i", $row->IP_Address)
                        ->setCellValue("K$i", $row->date_added);
                    $i++;
                }
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $objPHPExcel->setActiveSheetIndex(0);

        //SAVE THE FILE
        ob_clean();
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                $objWriter->save(__DIR__.'/Form Data.xlsx');

//                     ob_start();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Type: application/vnd.ms-excel; charset=utf-8");
                header("Content-Disposition: attachment;filename=\"Form Data.xlsx\"");
                //header("Content-Transfer-Encoding: binary ");
                // The following line outputs the file
                readfile(__DIR__.'/Form Data.xlsx');
                exit();
    }
    
    
function admin_webpinas_csv_download() {
    
       /** Error reporting */
//            error_reporting(E_ALL);
//            ini_set('display_errors', TRUE);
//            ini_set('display_startup_errors', TRUE);
           
        /** Include PHPExcel */
    
            require_once __DIR__.'/external_classes/Excel/Classes/PHPExcel.php';
            $objPHPExcel = new \PHPExcel();
        // var_dump($objPHPExcel);
            
            
        // Set document properties
            $objPHPExcel->getProperties()->setCreator("GymBus")
                                             ->setLastModifiedBy("GymBus")
                                             ->setTitle("GymBus Form Submission")
                                             ->setSubject("GymBus Form Submission")
                                             ->setDescription("This document contains the form submissions of your website.")
                                             ->setKeywords("GymBus")
                                             ->setCategory("GymBus");
        // Add some data
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'ID')
                        ->setCellValue('B1', 'Venue Address')
                        ->setCellValue('C1', 'Suburb')
                        ->setCellValue('D1', 'Post Code')
                        ->setCellValue('E1', 'Booking Date')
                        ->setCellValue('F1', 'Contact Name')
                        ->setCellValue('G1', 'Contact email')
                        ->setCellValue('H1', 'Contact Phone')
                        ->setCellValue('I1', 'Street Address')
                        ->setCellValue('J1', 'IP Address')
                        ->setCellValue('K1', 'Date Added');
            $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);

        // query to get the resuts from the database
            global $wpdb;

            $tablename = $wpdb->prefix.'google_location_form';
            $rows = $wpdb->get_results( "SELECT * FROM $tablename" );    

            $i=2;
            foreach ($rows as $row) {

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue("A$i", $row->id)
                        ->setCellValue("B$i", $row->venue_address)
                        ->setCellValue("C$i", $row->suburb)
                        ->setCellValue("D$i", $row->post_code)
                        ->setCellValue("E$i", $row->booking_date)
                        ->setCellValue("F$i", $row->contact_name)
                        ->setCellValue("G$i", $row->contact_email)
                        ->setCellValue("H$i", $row->contact_phone)
                        ->setCellValue("I$i", $row->street_address)
                        ->setCellValue("J$i", $row->IP_Address)
                        ->setCellValue("K$i", $row->date_added);
                    $i++;
                }
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $objPHPExcel->setActiveSheetIndex(0);

        //SAVE THE FILE
        ob_clean();
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
                $objWriter->save(__DIR__.'/Form Data.csv');

//                     ob_start();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment;filename=\"Form Data.csv\"");
                //header("Content-Transfer-Encoding: binary ");
                // The following line outputs the file
                readfile(__DIR__.'/Form Data.csv');
                exit();
    }


function admin_webpinas_glf_form() {
    ?>
   <div class="wrap nosubsub">
    
    <h1>Booking Form Data</h1>
    <br class="clear">
        <div id="col-container">
            <div id="col-right"><div class="col-wrap">
               <?php webpinas_glf_data_table_handler(); 
               webpinas_glf_view_details(); ?>
                </div>
            </div>
            <div id="col-left"><div class="col-wrap">
                <?php  
                webpinas_glf_field_form(); 
                webpinas_glf_add_more_fields();
                webpinas_glf_email_settings();
                ?>
                </div>
            </div>
        </div>        
   </div>
<?php
}

function webpinas_glf_view_details() {
    if (isset($_GET['view_id'])) {
        $id = $_GET ['view_id'];
        
         global $wpdb;
        $table_name = $wpdb->prefix . 'google_location_form';
        $row = $wpdb->get_row( "SELECT * FROM $table_name WHERE `id` = {$id}", ARRAY_A );
        $addational_fields = unserialize($row['form_dump']);
        
        ?>
                                                <br><h2>View Details</h2> <table class="widefat fixed" cellspacing="0" style="width:300px;">
    <thead>
    <tr>
            <th id="columnname" class="manage-column column-columnname" scope="col">Field Names</th>
            <th id="columnname" class="manage-column column-columnname num" scope="col">Values</th> 
    </tr>
    </thead>

    <tbody>
        <tr class="">
            <td class="column-columnname">Venue Address</td>
            <td class="column-columnname"><?php echo $row['venue_address']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Suburb</td>
            <td class="column-columnname"><?php echo $row['suburb']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Post Code</td>
            <td class="column-columnname"><?php echo $row['post_code']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Booking Date</td>
            <td class="column-columnname"><?php echo $row['booking_date']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Contact Name</td>
            <td class="column-columnname"><?php echo $row['contact_name']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Email</td>
            <td class="column-columnname"><?php echo $row['contact_email']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">Phone No</td>
            <td class="column-columnname"><?php echo $row['contact_phone']; ?></td>
        </tr>
        <tr>
            <td class="column-columnname">IP Address</td>
            <td class="column-columnname"><?php echo $row['IP_Address']; ?></td>
        </tr>
        <?php foreach ($addational_fields as $fieldname => $fieldvalue) { ?>
        <tr>
            <td class="column-columnname"><?php echo $fieldname; ?></td>
            <td class="column-columnname"><?php echo $fieldvalue; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
                                                <?php
        
    }
}

function webpinas_glf_field_form() {
    
    if (isset($_POST['action']) and $_POST['action']=="webpinas_update_fields_settings") {
                $options['name_field'] = trim( $_POST['name_field'] );
		$options['email_field']  = trim( $_POST['email_field'] );
		$options['phone_field'] = trim( $_POST['phone_field'] );
                $options['name_field_required'] = trim( $_POST['name_field_required'] );
		$options['email_field_required']  = trim( $_POST['email_field_required'] );
		$options['phone_field_required'] = trim( $_POST['phone_field_required'] );
                $options['redirect_page'] = trim( $_POST['redirect_page'] );
                $options['google_api_key'] = trim( $_POST['google_api_key'] );
                $options['enable_captcha'] = trim( $_POST['enable_captcha'] );
                $options['google_site_key'] = trim( $_POST['google_site_key'] );
                $options['google_secret_key'] = trim( $_POST['google_secret_key'] );
                $options['submit_button'] = trim( $_POST['submit_button'] );
                
		update_option( 'webpinas_glf_fields_settings', $options );
                unset($_POST);
                }
                
                $options                    = get_option('webpinas_glf_fields_settings');
                ?>
    <div class="wrap">
    <h2>Webpinas Locator Booking Form Settings Page</h2>
<!--    <div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>-->
    <form class="form-horizontal" action="" method="post" id="settings-form">
        <input type="hidden" name="action" value="webpinas_update_fields_settings"/>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="redirect_page">Redirect Page</label>
                </th>
                <td>
                  (Eg. /thankyou/)  <input type="text" name="redirect_page" id="name_field"  value='<?php if (isset($options['redirect_page'])) { echo $options['redirect_page'];} ?>' class=""> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="redirect_page">Rename Submit Button</label>
                </th>
                <td>
                  (Default: Secure your booking)  <input type="text" name="submit_button" id="name_field"  value='<?php if (isset($options['submit_button'])) { echo $options['submit_button'];} ?>' class=""> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="google_api_key">Google Map API key</label>
                </th>
                <td>
                  <input type="text"  style="width:100%" name="google_api_key" id="name_field"  value='<?php if (isset($options['google_api_key'])) { echo $options['google_api_key'];} ?>' class=""> 
                </td>
            </tr>
            <tr><th> 
                    <label class="control-label" for="enable_captcha">Enable Google Captcha</label>
                </th>
                <td><input type='checkbox' name='enable_captcha' value='1' <?php if (isset($options['enable_captcha']) and $options['enable_captcha'] == 1) { echo "checked";} ?> ></td>
            </tr>
            <tr><th> 
                    <label class="control-label" for="google_site_key">Google Captcha Site key</label>
                </th>
                <td><input style="width:100%" type="text" name="google_site_key" id="name_field"  value='<?php if (isset($options['google_site_key'])) { echo $options['google_site_key'];} ?>' class=""> </td>
            </tr>
            <tr><th> 
                    <label class="control-label" for="google_secret_key">Google Captcha Secret key</label>
                </th>
                <td><input style="width:100%" type="text" name="google_secret_key" id="name_field"  value='<?php if (isset($options['google_secret_key'])) { echo $options['google_secret_key'];} ?>' class=""> </td>
            </tr>
        </table>
        <h3>Add the following fields to the form.</h3>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="name_field">Name Field</label>
                </th>
                <td>
                    Add Field <input type="checkbox" name="name_field" id="name_field"  value='1' <?php if (isset($options['name_field']) and $options['name_field'] == 1) { echo "checked";} ?> class=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mark as required <input type="checkbox" name="name_field_required" id="name_field" value='1' <?php if (isset($options['name_field_required']) and $options['name_field_required'] == 1) { echo "checked";} ?> class=""> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="email_field">Email Field: </label>
                </th>
                <td>
                    Add Field <input type="checkbox" name="email_field" id="name_field"  value='1' <?php if (isset($options['email_field']) and $options['email_field'] == 1) { echo "checked";} ?> class=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mark as required <input type="checkbox" name="email_field_required" id="name_field" value='1' <?php if (isset($options['email_field_required']) and $options['email_field_required'] == 1) { echo "checked";} ?> class=""> 
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label class="control-label" for="phone_field">Phone No Field: </label>
                </th>
                <td>
                    Add Field <input type="checkbox" name="phone_field" id="name_field"  value='1' <?php if (isset($options['phone_field']) and $options['phone_field'] == 1) { echo "checked";} ?> class=""> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mark as required <input type="checkbox" name="phone_field_required" id="name_field" value='1' <?php if (isset($options['phone_field_required']) and $options['phone_field_required'] == 1) { echo "checked";} ?> class=""> 
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">Save Changes</button>
        </p>
    </form>
</div> <?php
}

function webpinas_glf_add_more_fields() {
    
     if (isset($_POST['action']) and $_POST['action']=="webpinas_glf_add_more_fields") {
         
         $field_label = filter_input(INPUT_POST, 'field_label');
         $field_label_replaced = str_replace(" ", "_", $field_label);
         $options_curr                    = get_option('webpinas_glf_add_more_fields');
                 if (is_array($options_curr)) {
                     $data = [ 'field_label' => $field_label_replaced,
                               'field_name' => $field_label,
                               'field_required' => $_POST['field_required'] ];
                     array_push($options_curr, $data);
                     update_option( 'webpinas_glf_add_more_fields', $options_curr );

                 }
                 else {
                     $data[0] = [ 'field_label' => $field_label_replaced,
                               'field_name' => $field_label,
                               'field_required' => $_POST['field_required'] ];
                     update_option( 'webpinas_glf_add_more_fields', $data );
                 }
            unset($_POST);	
        }
        elseif (isset($_POST['action']) and $_POST['action']=="webpinas_glf_delete_fields") {
                $delete_field = trim( $_POST['delete_field'] );
                $options_curr            = get_option('webpinas_glf_add_more_fields');
                array_splice ($options_curr, $delete_field, 1);
                if (empty($options_curr)) {
                    $options_curr =null;
                    }
                update_option( 'webpinas_glf_add_more_fields', $options_curr );
                unset($_POST);
        }
               
                $options                    = get_option('webpinas_glf_add_more_fields');
    ?>
    <div class="wrap">
    <!--<div id="updateDiv"><p><strong id="updateMessage"></strong></p></div>-->
    <form class="form-horizontal" action="" method="post" id="settings-form">
        <!--<p class="tips"></p>-->
        <input type="hidden" name="action" value="webpinas_glf_add_more_fields"/>
        <h3>Add additional form fields.</h3>
        <table class="form-table">
            <tr valign="top">
                <td>
                    Enter Field Label (No symbols or special characters) <input class='' type='text' name='field_label' value='<?php if (isset($options['field_label'])){ echo $options['field_label'];} ?>' /> <br> 
                <label class="control-label" for="field_required">Mark as Required field</label> <input type="checkbox" name="field_required" id="field_required" value='1' <?php if (isset($options['field_required']) and $options['field_required'] == 1) { echo "checked";} ?> class=""> 
                </td>
            </tr>
        </table>
            <p class="submit"><button type="submit" class="button button-primary">Add Field</button></p>
    </form>
    <?php if (is_array($options)) { ?>
     
        
        <h3>Current additional fields</h3>
        <table class="form-table">
            <tr valign="top">
                <th>Field  Name</th><th>Required</th><th>Delete</th>
            </tr>
            <?php $i=0; foreach ($options as $d) { ?>
            <tr valign="top">
                <td><?php echo $d['field_label']; ?></td><td><?php if ($d['field_required']==1){echo "Yes";}else{echo "No";} ?></td>
                <td><form class="form-horizontal" action="" method="post" id="settings-form"><input type="hidden" name="action" value="webpinas_glf_delete_fields"/>
     <input type="hidden" name="delete_field" value="<?php echo $i; ?>"/><button type="submit" class="button button-small">Delete</button></form></td>
            </tr>
            <?php $i++; } ?>
        </table>
        <?php } ?>
    
</div> <?php
    
}

function webpinas_glf_email_settings(){
    
    if (isset($_POST['action']) and $_POST['action']=="webpinas_glf_email_settings") {
                $options['webpinas_glf_email_notification'] = trim( $_POST['webpinas_glf_email_notification'] );
		$options['webpinas_glf_email_add']  = trim( $_POST['webpinas_glf_email_add'] );
		$options['webpinas_glf_email_template'] = trim( $_POST['webpinas_glf_email_template'] );
		update_option( 'webpinas_glf_email_settings', $options );
                unset($_POST);
                }
                
                $options                    = get_option('webpinas_glf_email_settings');
                
                
    ?>
                                                
        <h3>Settings to Enable/Disable email notifications for form submission.</h3>
        <div class="wrap"><form action="" method='post'>
        <input type="hidden" name="action" value="webpinas_glf_email_settings"/>
        <div>
            <input type='checkbox' name='webpinas_glf_email_notification' value='1' <?php if (isset($options['webpinas_glf_email_notification']) and $options['webpinas_glf_email_notification'] == 1) { echo "checked";} ?> >
            <span>Check to enable, or uncheck to disable email notification</span>    <br><br>
            <input class='' type='text' name='webpinas_glf_email_add' value='<?php if (isset($options['webpinas_glf_email_add'])){ echo $options['webpinas_glf_email_add'];} ?>' />
            <span>Enter your email addresses separated by comma</span>    <br><br>
            <strong><span>Use the following email template for your email notification</span></strong>  <br><br>
    <?php wp_editor($options['webpinas_glf_email_template'], "webpinas_glf_email_template", array()); ?>
        </div>
        <br>
        <p class="submit"><button type="submit" class="button button-primary">Update Email Settings</button></p>
    </form></div>

        <div>   <p><a href='/wp-admin/admin.php?page=admin_webpinas_excel_download'>Download Form Data Excel</a></p>
                <p><a href='/wp-admin/admin.php?page=admin_webpinas_csv_download'>Download Form Data CSV</a></p></div> 

<?php
}

/***********************
 * Admin Table
 * ===========
 * 
 * The following classes and functions will display our custom table
 *********************/

if (!class_exists('Form_WP_List_Table')) {
    require_once(  plugin_dir_path( __FILE__ ) .'class-wp-list-table.php');
}


class Custom_Table_webpinas_glf_Plugin extends Form_WP_List_Table
{
    /**
     * [REQUIRED] You must declare constructor and give some basic params
     */
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'ID',
            'plural' => 'IDs',
        ));
    }
    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%d" />',$item['id']);
    }
    function column_date_added($item)
    {
        return $item['date_added'];
    }
    function column_venue_address($item)
    {
        return $item['venue_address'];
    }
    function column_suburb($item)
    {
        return $item['suburb'];
    }
    function column_post_code($item)
    {
        return $item['post_code'];
    }
    function column_booking_date($item)
    {
        return $item['booking_date'];
    }
    function column_view_details($item)
    {
        return sprintf(
                 '<a href="?page=admin_webpinas_glf&view_id=%s">View Details</a>',
               $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    function get_columns()
    {      // global $hb_current_user_can_ap;
        $columns =
            ['cb' => '<input type="checkbox" />'] + //Render a checkbox instead of text
           //($a ? ['wp_username' => 'User'] : [] )+
            ['date_added' => 'Form submitted on']+    
            //['car_holder_name' => 'Name']+
            ['venue_address' => 'Venue Address']+
            ['suburb' => 'Suburb']+
            ['post_code' => 'Post Code']+
            ['booking_date' => 'Booking Date']+
            ['view_details' => 'View Details']
       ;
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    function get_sortable_columns()
    {
        $sortable_columns = array(
           'date_added' => array('date_added', false),
//            'wp_username' => array('wp_username', false),
//            'redirect_id' => array('redirect_id', false),
//            'redirect_destination' => array('redirect_destination', false),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'google_location_form'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'google_location_form'; // do not forget about tables prefix

        $per_page = 50; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
        //select only user own records if does not have activate_plugins capability
//        global $hb_current_user_can_ap;
//        if ($hb_current_user_can_ap == true) {
//            $user_sort=filter_var(@$_GET['s'], FILTER_VALIDATE_INT);
//            if (!empty($user_sort)) {$wherestatement = ' where wp_userid = '.$user_sort;}
//            else {$wherestatement='';}
//        }
//        else {
//            global $hb_current_user_id;
//            $wherestatement = ' where wp_userid = '.$hb_current_user_id;
//        }
    $wherestatement = '';
// will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $wherestatement");

        // prepare query params, as usual current page, order by and order direction
        //$paged = isset($_REQUEST['paged']) ? max(3, intval($_REQUEST['paged']) - 1) : 0;
        if(isset($_REQUEST['paged']))
                {$offset= (intval($_REQUEST['paged'])-1) * $per_page;}
        else {$offset = 0;}        
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';
        
        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve associative array
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name $wherestatement ORDER BY $orderby $order LIMIT %d, %d", $offset , $per_page), ARRAY_A);
        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
    
}


/**
 * List page handler
 *
 * This function renders our table
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function webpinas_glf_data_table_handler()
{
    global $wpdb;

    $table = new Custom_Table_webpinas_glf_Plugin();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'custom_table_example'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>

<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Form Submissions')?> 
        <!--<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=exc_quote_form');?>"><?php _e('Add new', 'custom_table_example')?></a>-->
    </h2>
    <?php echo $message; ?>
    
<!--<form method="get">
    <input type="hidden" name="page" value="">
<?php $table->dropdown_search_user( __( 'Search User' ), 'user_sort' ); ?>
</form>-->

    <form id="persons-table" method="GET">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>

<?php 
}
