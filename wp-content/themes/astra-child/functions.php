<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), '2.7', 'all' );
	wp_register_script( 'toc-jquery-script', get_stylesheet_directory_uri() . '/js/jquery-toc.js', array( 'jquery' ) );
		wp_register_script( 'testimonial-object-script', get_stylesheet_directory_uri() . '/js/testimonial-function.js', array( 'jquery') );
    wp_enqueue_script( 'toc-jquery-script' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
/*Include custom files*/
require_once 'astra-functions/astra-functions.php';
