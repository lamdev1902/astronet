<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/admin
 */
class WP_Calorie_Calculator_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Pro plugin deactivation.
	 *
	 * @since    2.0.0
	 */
	public function pro_deactivate() {
		if ( get_transient( 'wpcc_pro_deactivate' ) ) {
			deactivate_plugins( 'wp-calorie-calculator-pro/wp-calorie-calculator-pro.php' );
			delete_transient( 'wpcc_pro_deactivate' );
		}
	}

	/**
	 * Plugin activation notice.
	 *
	 * @since    1.0.0
	 */
	public function activation_notice() {
		if ( get_transient( 'wp_calorie_calculator_activation_notice' ) ) :
			?>
			<div class="notice notice-success updated is-dismissible">
				<p><?php echo __( 'Thank you for installing our WP Calorie Calculator plugin. The next step is to <a href="admin.php?page=wp-calorie-calculator">configure the settings of the plugin</a>.', 'wp-calorie-calculator' ); ?></p>
			</div>
			<?php
			delete_transient( 'wp_calorie_calculator_activation_notice' );
		endif;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		if ( $hook !== 'toplevel_page_' . $this->plugin_name ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-calorie-calculator-admin.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'toplevel_page_' . $this->plugin_name ) {
			return;
		}
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-calorie-calculator-admin.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'wpCalorieCalculatorI18n',
			array(
				'copied'        => __( 'Copied!', 'wp-calorie-calculator' ),
				'copyShortcode' => __( 'Copy shortcode', 'wp-calorie-calculator' ),
			)
		);
		wp_localize_script(
			$this->plugin_name,
			'adminajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Add plugin settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		add_menu_page(
			__( 'Create your Mifflin - St. Jeor Calorie Calculator', 'wp-calorie-calculator' ),
			__( 'Calorie Calculator', 'wp-calorie-calculator' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'settings_page_callback' ),
			'dashicons-calculator',
			null
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-title-hide' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-title-show' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-title-text' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-metric-system' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-instant-result' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-notification-email' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-primary-color' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc-secondary-color' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_user_agreements' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_privacy_policy' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_privacy_policy_url' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_privacy_policy_url_text' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_terms_and_conditions' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_terms_and_conditions_url' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_terms_and_conditions_url_text' );
		register_setting( 'wp-calorie-calculator-settings-group', 'wpcc_user_agreements_text' );
	}

	/**
	 * Plugin settings page markup.
	 *
	 * @since    1.0.0
	 */
	public function settings_page_callback() {
		ob_start();
		include 'partials/wp-calorie-calculator-admin-display.php';
		echo ob_get_clean();
	}

	/**
	 * Plugin settings link.
	 *
	 * @since    1.0.0
	 */
	public function plugin_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=wp-calorie-calculator">' . __( 'Settings', 'wp-calorie-calculator' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Pro version advertisement.
	 *
	 * @since    2.0.0
	 */
	public function pro_version_advertisement() {
		delete_option( 'wpcc_pro_version_announcement' ); // delete previous notice option from db

		if ( ! get_option( 'wpcc_pro_version_advertisement' ) ) :
			?>
			<div class="notice notice-success updated is-dismissible">
				<p style="font-size:15px;font-weight:500;"><?php echo __( 'The PRO edition of WP Calorie Calculator is out!', 'wp-calorie-calculator' ); ?></p>
				<p><?php echo __( 'Mailchimp, Zapier integration, Google reCAPTCHA, flexible calculation and style settings, custom templates, a fully customizable Elementor widget and other cool add-ons - try now and make your website users’ favorite place to be!', 'wp-calorie-calculator' ); ?></p>
				<p><a class="button" href="https://wpcaloriecalculator.com/?visitsource=wporgfree" target="_blank"><?php echo __( 'Get it', 'wp-calorie-calculator' ); ?></a></p>
			</div>
			<?php
			update_option( 'wpcc_pro_version_advertisement', true );
		endif;
	}

	/**
	 * Go Pro menu link.
	 *
	 * @since    2.0.0
	 */
	public function go_pro_menu() {
		add_submenu_page(
			$this->plugin_name,
			__( 'Go Pro', 'wp-calorie-calculator' ),
			__( 'Go Pro', 'wp-calorie-calculator' ),
			'manage_options',
			'#wpcc-go-pro',
			null,
			99
		);
	}

	/**
	 * Go Pro menu link script.
	 *
	 * @since    2.0.0
	 */
	public function go_pro_menu_script() {
		$html      = '<script>';
			$html .= "jQuery('#toplevel_page_wp-calorie-calculator .wp-submenu a[href*=wpcc-go-pro]').css('color', 'orange');";
			$html .= "jQuery('#toplevel_page_wp-calorie-calculator .wp-submenu a[href*=wpcc-go-pro]').css('font-weight', 'bold');";
			$html .= "jQuery('#toplevel_page_wp-calorie-calculator .wp-submenu a[href*=wpcc-go-pro]').attr('target', '_blank');";
			$html .= "jQuery('#toplevel_page_wp-calorie-calculator .wp-submenu a[href*=wpcc-go-pro]').attr('href', 'https://wpcaloriecalculator.com/?visitsource=wporgfree');";
		$html     .= '</script>';
		echo $html;
	}

	/**
	 * Add link to the PRO Version
	 *
	 * @param array $wpcc_settings_links   Plugin settings links.
	 * @since    3.0.0
	 */
	public function wpcc_pro_plugin_settings_link( $wpcc_settings_links ) {
		if ( ! get_transient( 'wpcc_pro_deactivate' ) ) {
			$pro_settings_link = '<a href="https://wpcaloriecalculator.com/?visitsource=wporgfree" style="font-weight: 600;color: red;" target="_blank">' . __( 'Get PRO', 'wp-calorie-calculator' ) . '</a>';
			array_unshift( $wpcc_settings_links, $pro_settings_link );

			return $wpcc_settings_links;
		}
	}

	/**
	 * Change color schema
	 *
	 * @since    3.0.0
	 */
	public function change_color_schema_callback() {
		// phpcs:ignore
		$color_schema = isset( $_POST['wpcc_primary_color'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcc_primary_color'] ) ) : '#325878';

		$wpcc               = new WP_Calorie_Calculator();
		$new_default_colors = $wpcc->get_calculator_default_colors( $color_schema );

		echo wp_json_encode( $new_default_colors );
		wp_die();
	}
}
