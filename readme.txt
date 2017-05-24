=== WP Responsive Menu ===
Contributors: webpinas
Tags: GoogleMaps
Requires at least: 
Tested up to: 4.6
Stable tag:

Webpinas Locator Booking Form plug-in is a google location form.

== Description ==

Webpinas Locator Booking Form plug-in creates a Google autocomplete location form. Allows you to add your own fields, and displays the submitted data in a nicely formated table in the settings menu of this plug-in.

== Important Notes for plugin settings page and general usage: ==

"Webpinas Locator Form Settings" menu is located on the left navigation bar of WordPress Dashboard.

= Google Map: =

1. In order for Google autocomplete function to work on the form, you need to add Google Map API key in the settings menu of the plugin. You can get a valid key by visiting the following link and clicking "GET A KEY" https://developers.google.com/maps/documentation/javascript/get-api-key
1. The plugin autocomplete is using Component Restrictions to strict the results to your area. Currently set to { country: 'au' } in script.js file in the plug-in directory. You can change the 'au' (Australia) to the code of your respective country. "ISO 3166-1 alpha-2" code are used, To get the code visit: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2  Google Docs in this regards are at https://developers.google.com/maps/documentation/javascript/examples/geocoding-component-restriction

= Google reCAPTCHA: =

To avoid spam submissions this plug-in is powered by Google reCAPTCHA. In order for reCAPTCHA to work you need to add google reCAPTCHA keys to the plug-in settings page. To get the keys visit the following link. https://www.google.com/recaptcha/admin#list

= Redirect Page: =

You can provide a Redirect Page option in the settings menu of the plugin to redirect to a given page after form submission.  

= form fields and additional fields options: =

Name, email, and phone number fields can be added by clicking the respective checkboxes in the settings menu. 
You can also add as many custom text fields as you want. Just enter the name of the fields and click "Add Field" button. Do not add special characters or symbols as html doc does not allow field names as symbols.
To delete a custom field click delete button.
If you want to make any field compulsory for the users, click the check box called "Mark as required".

= Email Option =

You can enter emails separated by comma in the email field to send the notification for form submissions. You also need to type an email template that you need to send. 
Use the following short codes if you need to add values to the template.

* [webpinas_venue_address]
* [webpinas_suburb]
* [webpinas_post_code]
* [webpinas_booking_date]
* [webpinas_contact_name]
* [webpinas_contact_email]
* [webpinas_contact_phone]
* [webpinas_ip_address]

== Installation ==

1. Go to your admin area and select Plugins -> Add new from the menu.
1. Search for "Webpinas Locator Booking Form".
1. Click install.
1. Click activate.
1. Once the plugin is installed then you can see Webpinas Locator Form Settings menu on the left navigation bar of WordPress Dashboard.