<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class WP_Simple_Featured_Image_Scripts{
	function __construct(){
		add_action( 'wp_enqueue_scripts', array( $this,'frontend_scripts' ) );
	}
	function frontend_scripts() {

		// Register Plugin styles
		wp_register_style( 'wpsfi-flexslider-styles', WPSFI_URL.'assets/css/flexslider.css', array(), WPSFI_VERSION ); 
		wp_register_style( 'wpsfi-animate-styles', WPSFI_URL.'assets/css/animate.css', array(), WPSFI_VERSION ); 
		wp_register_style( 'wpsfi-styles', WPSFI_URL.'assets/css/wpsfi-styles.css', array(), WPSFI_VERSION ); 
		
		// Enqueue PLugin Styles		
		wp_enqueue_style( 'wpsfi-flexslider-styles' );
		wp_enqueue_style( 'wpsfi-animate-styles' );
		wp_enqueue_style( 'wpsfi-styles' );

		// Register Plugin Scripts
		wp_register_script( 'wpsfi-flexslider-scripts', WPSFI_URL.'assets/js/jquery.flexslider.js', array('jquery'), WPSFI_VERSION );
		wp_register_script( 'wpsfi-easing-scripts', WPSFI_URL.'assets/js/jquery.easing.js', array('jquery'), WPSFI_VERSION );
		wp_register_script( 'wpsfi-mousewheel-scripts', WPSFI_URL.'assets/js/jquery.mousewheel.js', array('jquery'), WPSFI_VERSION );
		wp_register_script( 'wpsfi-flexslider-scripts', WPSFI_URL.'assets/js/jquery.flexslider.js', array('jquery'), WPSFI_VERSION );
		wp_register_script( 'wpsfi-scripts', WPSFI_URL.'assets/js/wpsfi-scripts.js', array('jquery'), WPSFI_VERSION );

		// Enqueue Plugin script
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wpsfi-flexslider-scripts' );
		wp_enqueue_script( 'wpsfi-easing-scripts' );
		wp_enqueue_script( 'wpsfi-mousewheel-scripts' );
		wp_enqueue_script( 'wpsfi-scripts' );
	}
}
new WP_Simple_Featured_Image_Scripts;