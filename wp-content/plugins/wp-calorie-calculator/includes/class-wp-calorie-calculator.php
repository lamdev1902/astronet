<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/includes
 */
class WP_Calorie_Calculator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Calorie_Calculator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_CALORIE_CALCULATOR_VERSION' ) ) {
			$this->version = WP_CALORIE_CALCULATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-calorie-calculator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Calorie_Calculator_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Calorie_Calculator_Admin. Defines all hooks for the admin area.
	 * - WP_Calorie_Calculator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-calorie-calculator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-calorie-calculator-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-calorie-calculator-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-calorie-calculator-public.php';

		$this->loader = new WP_Calorie_Calculator_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.3
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Calorie_Calculator_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Calorie_Calculator_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_file  = WP_CALORIE_CALCULATOR_PLUGIN_FILE;

		$this->loader->add_action( 'admin_init', $plugin_admin, 'pro_deactivate' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'pro_version_advertisement' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'activation_notice' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_filter( "plugin_action_links_{$plugin_file}", $plugin_admin, 'plugin_settings_link' );
		$this->loader->add_action( "plugin_action_links_{$plugin_file}", $plugin_admin, 'wpcc_pro_plugin_settings_link' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'go_pro_menu' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'go_pro_menu_script' );
		$this->loader->add_action( 'wp_ajax_change_color_schema', $plugin_admin, 'change_color_schema_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_change_color_schema', $plugin_admin, 'change_color_schema_callback' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Calorie_Calculator_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_wpcc_send_result', $plugin_public, 'send_result_on_email' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpcc_send_result', $plugin_public, 'send_result_on_email' );
		add_shortcode( 'cal_calc', array( $plugin_public, 'shortcode_callback' ) );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_Calorie_Calculator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Increases or decreases the brightness of a color by a percentage of the current brightness.
	 *
	 * @param   string $hex_code        Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`.
	 * @param   float  $adjust_percent  A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
	 *
	 * @since     3.0.0
	 * @return  string
	 */
	public function adjust_brightness( $hex_code, $adjust_percent ) {
		$hex_code = ltrim( $hex_code, '#' );

		if ( 3 === strlen( $hex_code ) ) {
			$hex_code = $hex_code[0] . $hex_code[0] . $hex_code[1] . $hex_code[1] . $hex_code[2] . $hex_code[2];
		}

		$hex_code = array_map( 'hexdec', str_split( $hex_code, 2 ) );

		foreach ( $hex_code as & $color ) :
			$adjustable_limit = $adjust_percent < 0 ? $color : 255 - $color;
			$adjust_amount    = ceil( $adjustable_limit * $adjust_percent );

			$color = str_pad( dechex( $color + $adjust_amount ), 2, '0', STR_PAD_LEFT );
		endforeach;

		return '#' . implode( $hex_code );
	}

	/**
	 * Calculator layouts colors.
	 *
	 * @param   string $main_color        Supported formats: `#FFF`, `#FFFFFF`.
	 *
	 * @since    3.0.0
	 */
	public function get_calculator_default_colors( $main_color = '#00B5AD' ) {

		$layouts_colors = array(
			'two_compact_pretty' => array(
				'main_title_color'              => array(
					'name'          => __( 'Main title color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, -0.8 ),
				),
				'main_title_border_color'       => array(
					'name'          => __( 'Main title border color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, 0.9 ),
				),
				'text_color'                    => array(
					'name'          => __( 'Text color', 'wp-calorie-calculator' ),
					'default_color' => '#1c1f23',
				),
				'switcher_circle_color'         => array(
					'name'          => __( 'Switcher circle color', 'wp-calorie-calculator' ),
					'default_color' => '#ffffff',
				),
				'switcher_background_color'     => array(
					'name'          => __( 'Switcher background color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'labels_color'                  => array(
					'name'          => __( 'Labels color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, -0.8 ),
				),
				'radio_button_color'            => array(
					'name'          => __( 'Radio button color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, -0.4 ),
				),
				'tooltip_icon_color'            => array(
					'name'          => __( 'Tooltip icon color', 'wp-calorie-calculator' ),
					'default_color' => '#A7ABB0',
				),
				'border_color'                  => array(
					'name'          => __( 'Input border color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'result_background_color'       => array(
					'name'          => __( 'Result background color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, 0.85 ),
				),
				'result_title_color'            => array(
					'name'          => __( 'Result title color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'result_text_color'             => array(
					'name'          => __( 'Result text color', 'wp-calorie-calculator' ),
					'default_color' => '#1c1f23',
				),
				'result_icon_background'        => array(
					'name'          => __( 'Result icon background', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'result_icon_color'             => array(
					'name'          => __( 'Result icon color', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, 0.85 ),
				),
				'button_background_color'       => array(
					'name'          => __( 'Button background color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'button_background_color_hover' => array(
					'name'          => __( 'Button background color on hover', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, -0.3 ),
				),
				'button_text_color'             => array(
					'name'          => __( 'Button text color', 'wp-calorie-calculator' ),
					'default_color' => '#ffffff',
				),
				'form_checkbox_color'           => array(
					'name'          => __( 'Form checkbox color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'form_link_color'               => array(
					'name'          => __( 'Form links color', 'wp-calorie-calculator' ),
					'default_color' => $main_color,
				),
				'form_link_color_hover'         => array(
					'name'          => __( 'Form links color on hover', 'wp-calorie-calculator' ),
					'default_color' => $this->adjust_brightness( $main_color, -0.3 ),
				),
			),
		);

		return $layouts_colors;
	}

		/**
		 * Calculator default goals.
		 *
		 * @since    3.0.0
		 */
	public function get_calculator_default_goals() {
		$goals = array(
			array(
				'name'        => __( 'Maintain Weight', 'wp-calorie-calculator' ),
				'coefficient' => 1,
			),
			array(
				'name'        => __( 'Mild Weight Loss', 'wp-calorie-calculator' ),
				'coefficient' => 0.9,
			),
			array(
				'name'        => __( 'Weight Loss', 'wp-calorie-calculator' ),
				'coefficient' => 0.8,
			),
			array(
				'name'        => __( 'Mild Weight Gain', 'wp-calorie-calculator' ),
				'coefficient' => 1.1,
			),
			array(
				'name'        => __( 'Weight Gain', 'wp-calorie-calculator' ),
				'coefficient' => 1.2,
			),
		);

		return $goals;
	}

	/**
	 * Calculator default activity.
	 *
	 * @since    3.0.0
	 */
	public function get_calculator_activity() {
		$activity = array(
			array(
				'name'        => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
				'coefficient' => 1,
			),
			array(
				'name'        => esc_html__( 'Sedentary: little or no exercise', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Spend most of the day sitting, with little or no exercise', 'wp-calorie-calculator' ),
				'coefficient' => 1.2,
			),
			array(
				'name'        => esc_html__( 'Light: exercise 1-3 times/week', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Exercise 1-3 times/week', 'wp-calorie-calculator' ),
				'coefficient' => 1.375,
			),
			array(
				'name'        => esc_html__( 'Moderate: exercise 4-5 times/week', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Exercise 4-5 times/week', 'wp-calorie-calculator' ),
				'coefficient' => 1.465,
			),
			array(
				'name'        => esc_html__( 'Active: daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
				'coefficient' => 1.55,
			),
			array(
				'name'        => esc_html__( 'Very Active: intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
				'coefficient' => 1.725,
			),
			array(
				'name'        => esc_html__( 'Extra Active: very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
				'description' => esc_html__( 'Very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
				'coefficient' => 1.9,
			),
		);

		return $activity;
	}

}
