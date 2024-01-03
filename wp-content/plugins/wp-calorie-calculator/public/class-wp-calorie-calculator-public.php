<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/public
 */
class WP_Calorie_Calculator_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-calorie-calculator-public.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-calorie-calculator-public.min.js', array( 'jquery' ), $this->version, true );
		wp_localize_script(
			$this->plugin_name,
			'wpCalorieCalculatorI18n',
			array(
				'emailSendSuccess' => __( 'Calorie Calculator results have been successfully sent to your email address!', 'wp-calorie-calculator' ),
				'emailSendFail'    => __( 'An error has occurred.', 'wp-calorie-calculator' ),
				'emptyFields'      => __( 'Please, fill in all fields correctly.', 'wp-calorie-calculator' ),
			)
		);
	}

	/**
	 * Shortcode callback function.
	 *
	 * @since    1.0.0
	 * @return   string    html markup of calculator.
	 */
	public function shortcode_callback() {
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( $this->plugin_name );

		ob_start();
		include 'partials/wp-calorie-calculator-public-display.php';
		return ob_get_clean();
	}

	/**
	 * Ajax handler for sending the result to email.
	 *
	 * @since    1.0.0
	 */
	public function send_result_on_email() {
		$server_response = array();

		// Input data.
		$user_email         = isset( $_POST['user_email'] ) ? sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) : '';
		$notification_email = ! empty( get_option( 'wpcc-notification-email' ) ) ? get_option( 'wpcc-notification-email' ) : get_option( 'admin_email' );
		$result             = isset( $_POST['result'] ) ? sanitize_text_field( wp_unslash( $_POST['result'] ) ) : '';
		$metric_system      = isset( $_POST['fields']['metric_system'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['metric_system'] ) ) : '';
		$goal               = isset( $_POST['fields']['goal'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['goal'] ) ) : '';
		switch ( $goal ) {
			case 'Maintain Weight':
				$goal = __( 'Maintain Weight', 'wp-calorie-calculator' );
				break;
			case 'Weight Loss"':
				$goal = __( 'Weight Loss', 'wp-calorie-calculator' );
				break;
			case 'Extreme Weight Loss':
				$goal = __( 'Extreme Weight Loss', 'wp-calorie-calculator' );
				break;
			case 'Weight Gain':
				$goal = __( 'Weight Gain', 'wp-calorie-calculator' );
				break;
			case 'Fast Weight Gain':
				$goal = __( 'Fast Weight Gain', 'wp-calorie-calculator' );
				break;
		}
		$unit   = $metric_system ? 'Metric' : 'Imperial';
		$gender = isset( $_POST['fields']['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['gender'] ) ) : '';
		switch ( $gender ) {
			case 'male':
				$gender = __( 'Male', 'wp-calorie-calculator' );
				break;
			case 'female':
				$gender = __( 'Female', 'wp-calorie-calculator' );
				break;
		}
		$age           = isset( $_POST['fields']['age'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['age'] ) ) : '';
		$height        = isset( $_POST['fields']['height'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['height'] ) ) : '';
		$height2       = isset( $_POST['fields']['height2'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['height2'] ) ) : '';
		$height_string = $metric_system == 'true' ? $height . __( 'cm', 'wp-calorie-calculator' ) : $height . __( 'ft', 'wp-calorie-calculator' ) . ' ' . $height2 . __( 'in', 'wp-calorie-calculator' );
		$weight        = isset( $_POST['fields']['weight'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['weight'] ) ) : '';
		$weight_string = $metric_system == 'true' ? $weight . __( 'kg', 'wp-calorie-calculator' ) : $weight . __( 'lbs', 'wp-calorie-calculator' );
		$activity      = isset( $_POST['fields']['activity'] ) ? sanitize_text_field( wp_unslash( $_POST['fields']['activity'] ) ) : '';
		switch ( $activity ) {
			case 'Sedentary':
				$activity = __( 'Sedentary', 'wp-calorie-calculator' );
				break;
			case 'Light':
				$activity = __( 'Light', 'wp-calorie-calculator' );
				break;
			case 'Moderate':
				$activity = __( 'Moderate', 'wp-calorie-calculator' );
				break;
			case 'Active':
				$activity = __( 'Active', 'wp-calorie-calculator' );
				break;
			case 'Very Active':
				$activity = __( 'Very Active', 'wp-calorie-calculator' );
				break;
			case 'Extra Active':
				$activity = __( 'Extra Active', 'wp-calorie-calculator' );
				break;
		}

		// Email to user.
		$subject                            = __( 'Your optimal calories', 'wp-calorie-calculator' );
		$message                            = __( 'Hi!', 'wp-calorie-calculator' ) . "\n";
		$message                           .= __( 'It’s Calorie Calculator.', 'wp-calorie-calculator' ) . "\n";
		$message                           .= __( 'Looks like you requested your target daily calorie intake.', 'wp-calorie-calculator' ) . "\n";
		$message                           .= sprintf( __( 'It is %s', 'wp-calorie-calculator' ), $result ) . "\n\n";
		$message                           .= __( 'Your parameters:', 'wp-calorie-calculator' ) . "\n\n";
		$message                           .= sprintf( __( 'Sex: %s', 'wp-calorie-calculator' ), $gender ) . "\n";
		$message                           .= sprintf( __( 'Age: %s', 'wp-calorie-calculator' ), $age ) . "\n";
		$message                           .= sprintf( __( 'Height: %s', 'wp-calorie-calculator' ), $height_string ) . "\n";
		$message                           .= sprintf( __( 'Weight: %s', 'wp-calorie-calculator' ), $weight_string ) . "\n";
		$message                           .= sprintf( __( 'Activity level: %s', 'wp-calorie-calculator' ), $activity ) . "\n";
		$message                           .= sprintf( __( 'Goal: %s', 'wp-calorie-calculator' ), $goal ) . "\n\n";
		$message                           .= __( 'You go!', 'wp-calorie-calculator' ) . "\n\n";
		$message                           .= __( 'Best regards,', 'wp-calorie-calculator' ) . "\n";
		$message                           .= __( 'Calorie Calculator.', 'wp-calorie-calculator' );
		$server_response['user_email_sent'] = wp_mail( $user_email, $subject, $message );

		// Notification email.
		$subject                                    = __( 'New Calorie Calculator user', 'wp-calorie-calculator' );
		$message                                    = __( 'Hey, someone just shared their email address with you.', 'wp-calorie-calculator' ) . "\n";
		$message                                   .= sprintf( __( 'Here it is: %s', 'wp-calorie-calculator' ), $user_email ) . "\n\n";
		$message                                   .= __( 'Make it the beginning of your brand’s active conversation.', 'wp-calorie-calculator' ) . "\n\n";
		$message                                   .= __( 'Best regards,', 'wp-calorie-calculator' ) . "\n";
		$message                                   .= __( 'Calorie Calculator.', 'wp-calorie-calculator' );
		$server_response['notification_email_sent'] = wp_mail( $notification_email, $subject, $message );

		echo wp_json_encode( $server_response );
		wp_die();
	}

}
