<?php

/**
 * Fired during plugin activation
 *
 * @link       http://syllogic.in
 * @since      1.0.0
 *
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cf7_2_Post
 * @subpackage Cf7_2_Post/includes
 * @author     Aurovrata V. <vrata@syllogic.in>
 */
class Cf7_2_Post_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
    //no need to make any special actionvation for multisite.
    //check if the dependent plugins are active
    if(!is_plugin_active( 'contact-form-7/wp-contact-form-7.php' )){
      if(is_multisite()){
        exit('Contact Form 7 plugin needs to be activated first. If you have activated it on select sites,
        you will need to activate the Post My CF7 Form plugin on those sites only');
      }
      exit('This plugin requires the Contact Form 7 plugin to be installed first');
    }
	}

}
