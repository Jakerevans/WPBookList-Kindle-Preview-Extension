<?php
/**
 * WPBookList WPBookList_Kindle_Form Submenu Class
 *
 * @author   Jake Evans
 * @category ??????
 * @package  ??????
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPBookList_Kindle_Form', false ) ) :
/**
 * WPBookList_Kindle_Form Class.
 */
class WPBookList_Kindle_Form {

	public static function output_kindle_form(){

		global $wpdb;
	
		// For grabbing an image from media library
		wp_enqueue_media();

		$string1 = '';
		
    	return $string1;
	}
}

endif;