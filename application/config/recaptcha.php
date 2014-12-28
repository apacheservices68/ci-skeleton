<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * reCaptcha File Config
 *
 * File     : recaptcha.php
 * Created  : May 14, 2013 | 4:22:40 PM
 * 
 * Author   : Andi Irwandi Langgara <irwandi@ourbluecode.com>
 */
#6LeeQf4SAAAAAMB0iJ8xlIpb8tJxMvNxn9mLTACx
#6LeeQf4SAAAAAGuL5eNUUxF6rMtuJZltNDVZsLVl

$config['public_key']   = '6Lc3gP4SAAAAAPe_4JWv0siA-i3saTFFQUxRxnKy';
$config['private_key']  = '6Lc3gP4SAAAAABwKGMgBDlVFL460-NykNWvj8Nys';

// Set Recaptcha options
// Reference at https://developers.google.com/recaptcha/docs/customization
$config['recaptcha_options']  = array(
    'theme'=>'clean', // red/white/blackglass/clean
    'lang' => 'vi' // en/nl/fl/de/pt/ru/es/tr
    //  'custom_translations' - Use this to specify custom translations of reCAPTCHA strings.
    //  'custom_theme_widget' - When using custom theming, this is a div element which contains the widget. See the custom theming section for how to use this.
    //  'tabindex' - Sets a tabindex for the reCAPTCHA text box. If other elements in the form use a tabindex, this should be set so that navigation is easier for the user
);

?>
