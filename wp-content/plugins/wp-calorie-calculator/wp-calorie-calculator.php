<?php
/**
 * The plugin bootstrap file
 *
 * @since             1.0.0
 * @package           WP_Calorie_Calculator
 *
 * @wordpress-plugin
 * Plugin Name:       WP Calorie Calculator
 * Description:       Calorie Calculator gives you the shortcode with the flexible settings that you can place into the page, post or sidebar widget. Or actually anywhere you can place the shortcode.
 * Version:           4.0.12
 * Requires at least: 4.7
 * Requires PHP:      5.6
 * Author:            Belov Digital Agency
 * Author URI:        https://belovdigital.agency
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-calorie-calculator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin constants
 */
define( 'WP_CALORIE_CALCULATOR_VERSION', '4.0.12' );
define( 'WP_CALORIE_CALCULATOR_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'WP_CALORIE_CALCULATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/wp-calorie-calculator-activator.php
 */
function activate_wp_calorie_calculator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/wp-calorie-calculator-activator.php';
	WP_Calorie_Calculator_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_wp_calorie_calculator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-calorie-calculator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_calorie_calculator() {

	$plugin = new WP_Calorie_Calculator();
	$plugin->run();

}
run_wp_calorie_calculator();
