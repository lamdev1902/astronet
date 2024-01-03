<?php

/**
 * Fired during plugin activation
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/includes
 */
class WP_Calorie_Calculator_Activator {

	/**
	 * Main activation method
	 *
	 * @since    1.0.0
	 * @version  2.0.0
	 */
	public static function activate() {

		// Deactivate pro version of plugin
		if ( is_plugin_active( 'wp-calorie-calculator-pro/wp-calorie-calculator-pro.php' ) ){
			set_transient( 'wpcc_pro_deactivate', true, 5 );
		}

    set_transient( 'wp_calorie_calculator_activation_notice', true, 5 );

  }
  
}