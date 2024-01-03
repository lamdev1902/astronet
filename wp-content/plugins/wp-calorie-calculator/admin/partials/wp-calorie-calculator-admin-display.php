<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/admin/partials
 */

$wpcc               = new WP_Calorie_Calculator();
$title_hide         = get_option( 'wpcc-title-hide', '' );
$title_show         = get_option( 'wpcc-title-show', ! $title_hide );
$title_text         = get_option( 'wpcc-title-text', __( 'CALCULATE YOUR OPTIMAL CALORIES', 'wp-calorie-calculator' ) );
$metric_system      = get_option( 'wpcc-metric-system', '' );
$instant_result     = get_option( 'wpcc-instant-result', '' );
$notification_email = ! empty( get_option( 'wpcc-notification-email' ) ) ? get_option( 'wpcc-notification-email' ) : get_option( 'admin_email' );

$primary_color   = get_option( 'wpcc-primary-color', '#325878' );
$secondary_color = get_option( 'wpcc-secondary-color', '#4989BE' );
$layout_style    = 'two_compact_pretty';
$default_colors  = $wpcc->get_calculator_default_colors( $primary_color );

$urlparts       = wp_parse_url( site_url() );
$current_domain = $urlparts['host'];

$activity_levels = array(
	array(
		'name'        => __( 'Sedentary', 'wp-calorie-calculator' ),
		'description' => __( 'Spend most of the day sitting, with little or no exercise', 'wp-calorie-calculator' ),
		'coefficient' => 1.2,
	),
	array(
		'name'        => __( 'Light', 'wp-calorie-calculator' ),
		'description' => __( 'Exercise 1-3 times/week', 'wp-calorie-calculator' ),
		'coefficient' => 1.375,
	),
	array(
		'name'        => __( 'Moderate', 'wp-calorie-calculator' ),
		'description' => __( 'Exercise 4-5 times/week', 'wp-calorie-calculator' ),
		'coefficient' => 1.465,
	),
	array(
		'name'        => __( 'Active', 'wp-calorie-calculator' ),
		'description' => __( 'Daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
		'coefficient' => 1.55,
	),
	array(
		'name'        => __( 'Very Active', 'wp-calorie-calculator' ),
		'description' => __( 'Intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
		'coefficient' => 1.725,
	),
	array(
		'name'        => __( 'Extra Active', 'wp-calorie-calculator' ),
		'description' => __( 'Very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
		'coefficient' => 1.9,
	),
);

$goals = array(
	array(
		'name'        => __( 'Maintain Weight', 'wp-calorie-calculator' ),
		'carbs'       => 40,
		'protein'     => 30,
		'fats'        => 30,
		'coefficient' => 1,
	),
	array(
		'name'        => __( 'Mild Weight Loss', 'wp-calorie-calculator' ),
		'carbs'       => 30,
		'protein'     => 45,
		'fats'        => 25,
		'coefficient' => 0.9,
	),
	array(
		'name'        => __( 'Weight Loss', 'wp-calorie-calculator' ),
		'carbs'       => 20,
		'protein'     => 50,
		'fats'        => 30,
		'coefficient' => 0.8,
	),
	array(
		'name'        => __( 'Mild Weight Gain', 'wp-calorie-calculator' ),
		'carbs'       => 45,
		'protein'     => 30,
		'fats'        => 25,
		'coefficient' => 1.1,
	),
	array(
		'name'        => __( 'Weight Gain', 'wp-calorie-calculator' ),
		'carbs'       => 50,
		'protein'     => 25,
		'fats'        => 25,
		'coefficient' => 1.2,
	),
);

$font_sizes = array(
	array(
		'name'  => esc_html__( 'Small', 'wp-calorie-calculator' ),
		'value' => '14px',
	),
	array(
		'name'  => esc_html__( 'Normal', 'wp-calorie-calculator' ),
		'value' => '16px',
	),
	array(
		'name'  => esc_html__( 'Large', 'wp-calorie-calculator' ),
		'value' => '18px',
	),
	array(
		'name'  => esc_html__( 'Extra Large', 'wp-calorie-calculator' ),
		'value' => '20px',
	),
);

$wpcc_email_user_subject = esc_html__( 'Your optimal calories', 'wp-calorie-calculator' );
/* translators: %s: user_name */
$wpcc_email_user_body  = sprintf( esc_html__( 'Hi, %s!', 'wp-calorie-calculator' ), '{user_name}' ) . "\n";
$wpcc_email_user_body .= esc_html__( 'It’s Calorie Calculator.', 'wp-calorie-calculator' ) . "\n";
$wpcc_email_user_body .= esc_html__( 'Looks like you requested your target daily calorie intake.', 'wp-calorie-calculator' ) . "\n";
/* translators: %s: result */
$wpcc_email_user_body .= sprintf( esc_html__( 'It is %s calories per day', 'wp-calorie-calculator' ), '{result}' ) . "\n\n";
/* translators: %s: index_bmi */
$wpcc_email_user_body .= sprintf( esc_html__( 'Body Mass Index (BMI): %s', 'wp-calorie-calculator' ), '{index_bmi}' ) . "\n";
/* translators: %s: bmi_class */
$wpcc_email_user_body .= sprintf( esc_html__( 'BMI Classification: %s', 'wp-calorie-calculator' ), '{bmi_class}' ) . "\n";
/* translators: %s: index_bmr */
$wpcc_email_user_body .= sprintf( esc_html__( 'Basal Metabolic Rate (BMR): %s calories per day', 'wp-calorie-calculator' ), '{index_bmr}' ) . "\n\n";
$wpcc_email_user_body .= esc_html__( 'Macronutrient Balance:', 'wp-calorie-calculator' ) . "\n";
/* translators: %s: fats */
$wpcc_email_user_body .= sprintf( esc_html__( 'Fats: %s g', 'wp-calorie-calculator' ), '{fats}' ) . "\n";
/* translators: %s: protein */
$wpcc_email_user_body .= sprintf( esc_html__( 'Protein: %s g', 'wp-calorie-calculator' ), '{protein}' ) . "\n";
/* translators: %s: carbs */
$wpcc_email_user_body .= sprintf( esc_html__( 'Carbs: %s g', 'wp-calorie-calculator' ), '{carbs}' ) . "\n\n";
$wpcc_email_user_body .= esc_html__( 'Your parameters:', 'wp-calorie-calculator' ) . "\n\n";
/* translators: %s: sex */
$wpcc_email_user_body .= sprintf( esc_html__( 'Sex: %s', 'wp-calorie-calculator' ), '{sex}' ) . "\n";
/* translators: %s: age */
$wpcc_email_user_body .= sprintf( esc_html__( 'Age: %s', 'wp-calorie-calculator' ), '{age}' ) . "\n";
/* translators: %s: height */
$wpcc_email_user_body .= sprintf( esc_html__( 'Height: %s', 'wp-calorie-calculator' ), '{height}' ) . "\n";
/* translators: %s: weight */
$wpcc_email_user_body .= sprintf( esc_html__( 'Weight: %s', 'wp-calorie-calculator' ), '{weight}' ) . "\n";
/* translators: %s: activity */
$wpcc_email_user_body .= sprintf( esc_html__( 'Activity level: %s', 'wp-calorie-calculator' ), '{activity}' ) . "\n";
/* translators: %s: goal */
$wpcc_email_user_body .= sprintf( esc_html__( 'Goal: %s', 'wp-calorie-calculator' ), '{goal}' ) . "\n\n";
$wpcc_email_user_body .= esc_html__( 'Keep up the good work!', 'wp-calorie-calculator' ) . "\n\n";
$wpcc_email_user_body .= esc_html__( 'Best regards,', 'wp-calorie-calculator' ) . "\n";
$wpcc_email_user_body .= esc_html__( 'Calorie Calculator.', 'wp-calorie-calculator' );

$wpcc_email_admin_subject = esc_html__( 'New Calorie Calculator user', 'wp-calorie-calculator' );

$wpcc_email_admin_body = esc_html__( 'Hey, someone just shared their email address with you.', 'wp-calorie-calculator' ) . "\n";
/* translators: %s: user_name */
$wpcc_email_admin_body .= sprintf( esc_html__( 'User\'s name: %s', 'wp-calorie-calculator' ), '{user_name}' ) . "\n";
/* translators: %s: user_email */
$wpcc_email_admin_body .= sprintf( esc_html__( 'User\'s email: %s', 'wp-calorie-calculator' ), '{user_email}' ) . "\n\n";
$wpcc_email_admin_body .= esc_html__( 'Make it the beginning of your brand’s active conversation.', 'wp-calorie-calculator' ) . "\n\n";
$wpcc_email_admin_body .= esc_html__( 'Best regards,', 'wp-calorie-calculator' ) . "\n";
$wpcc_email_admin_body .= esc_html__( 'Calorie Calculator.', 'wp-calorie-calculator' );

// Privacy Policy Block.
$wpcc_user_agreements               = get_option( 'wpcc_user_agreements', '' );
$wpcc_privacy_policy                = get_option( 'wpcc_privacy_policy', '' );
$wpcc_privacy_policy_url            = get_option( 'wpcc_privacy_policy_url', '' );
$wpcc_privacy_policy_url_text       = get_option( 'wpcc_privacy_policy_url_text', __( 'Privacy Policy', 'wp-calorie-calculator' ) );
$wpcc_terms_and_conditions          = get_option( 'wpcc_terms_and_conditions', '' );
$wpcc_terms_and_conditions_url      = get_option( 'wpcc_terms_and_conditions_url', '' );
$wpcc_terms_and_conditions_url_text = get_option( 'wpcc_terms_and_conditions_url_text', __( 'Terms and Conditions', 'wp-calorie-calculator' ) );

$wpcc_user_agreements_text = get_option( 'wpcc_user_agreements_text', __( 'Please accept our Privacy Policy and Terms & Conditions to proceed.', 'wp-calorie-calculator' ) );
?>

<form method="post" action="options.php" novalidate>
	<?php settings_fields( 'wp-calorie-calculator-settings-group' ); ?>

	<div class="wpcc-settings">

		<div class="wpcc-settings-wrapper">

			<div class="wpcc-settings-content">
				<div class="wpcc-settings-header">
					<?php wp_nonce_field( 'wpcc-nonce-' . get_the_ID(), 'wpcc_nonce' ); ?>
					<h1 class="wpcc-settings-header-title"><?php echo esc_attr( get_admin_page_title() ); ?></h1>
					<div class="wpcc-description">
						<p><?php esc_attr_e( '1. Choose the settings you\'d like to use for this specified instance of the shortcode on your website (widget, a particular page, or something else).', 'wp-calorie-calculator' ); ?><br>
						<?php esc_attr_e( '2. Copy the shortcode and paste it wherever you need.', 'wp-calorie-calculator' ); ?></p>
					</div>
					<div class="wpcc-shortcode-mob"></div>				

				</div>

				<div class="wpcc-settings-tabs">

					<ul id="wpcc-settings-tab-links">
						<li>
							<div class="wpcc-shortcode-desktop">
								<div class="wpcc-shortcode">
									<button class="wpcc-shortcode-title" type="button"><?php esc_attr_e( 'Your Shortcode', 'wp-calorie-calculator' ); ?></button>
									<div class="wpcc-shortcode-result-wrapper">
										<textarea class="wpcc-shortcode-result" rows="1" readonly>[cal_calc]</textarea>
										<button class="wpcc-shortcode-copy wpcc-tooltip" type="button">
											<div class="wpcc-tooltip-text"><?php esc_attr_e( 'Copy shortcode', 'wp-calorie-calculator' ); ?></div>
										</button>
									</div>
								</div>	
							</div>																
						</li>
						<li>
							<a href="#main-settings" class="active">
								<span><?php esc_attr_e( 'Settings', 'wp-calorie-calculator' ); ?></span>
								<svg>
									<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'admin/images/settings-main.svg#main' ); ?>"></use>
								</svg>							
							</a>
						<li>
							<a href="#calculation-settings">
								<span><?php esc_attr_e( 'Calculation', 'wp-calorie-calculator' ); ?></span>
								<svg>
									<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'admin/images/settings-calculation.svg#calc' ); ?>"></use>
								</svg>
							</a>
						</li>
						<li>
							<a href="#styling-settings">
								<span><?php esc_attr_e( 'Styling', 'wp-calorie-calculator' ); ?></span>
								<svg>
									<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'admin/images/settings-styling.svg#styling' ); ?>"></use>
								</svg>
						</a>
						</li>
						<li>
							<a href="#templates-settings">
								<span><?php esc_attr_e( 'Email templates', 'wp-calorie-calculator' ); ?></span>
								<svg>
									<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'admin/images/settings-email.svg#email' ); ?>"></use>
								</svg>
							</a>
						</li>
					</ul>

					<div class="wpcc-settings-tabs-wrapper">

						<section id="main-settings" class="wpcc-settings-section active">
							<h2 class="wpcc-settings-section-title"><?php esc_attr_e( 'Settings', 'wp-calorie-calculator' ); ?></h2>				

							<div class="wpcc-settings-group">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Main Calculator Title', 'wp-calorie-calculator' ); ?></h3>

									<div class="wpcc-switch-wrapper">
										<div class="wpcc-switch">
											<div class="wpcc-switch-option" data-position="left">	<?php esc_attr_e( 'Hide', 'wp-calorie-calculator' ); ?></div>
											<label class="wpcc-switch-toggle">
												<input type="checkbox" name="wpcc-title-show" value="true" <?php checked( 'true', $title_show ); ?>>
												<div class="wpcc-switch-toggle-circle"></div>
												<div class="wpcc-switch-toggle-background"></div>
											</label>
											<div class="wpcc-switch-option" data-position="right"><?php esc_attr_e( 'Show', 'wp-calorie-calculator' ); ?></div>
										</div>
									</div>						
								</div>

								<div class="wpcc-settings-group-content wpcc-title-text-wrapper"
									style="<?php echo $title_show ? '' : 'display:none;'; ?>">

									<div class="wpcc-settings-group-content">

										<div class="wpcc-settings-row wpcc-settings-row-title">
											<input type="text" name="wpcc-title-text" value="<?php echo esc_attr( $title_text ); ?>">
											<div class="wpcc-settings-row block-disabled">
												<div class="wpcc-settings-description"><?php esc_attr_e( 'Title align', 'wp-calorie-calculator' ); ?>
												</div>
												<div class="wpcc-settings-pro">PRO
													<div class="wpcc-settings-pro-tooltip">
														<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
													</div>
												</div>
												<select name="wpcc_title_align" disabled>
													<option selected disabled><?php esc_attr_e( 'Center', 'wp-calorie-calculator' ); ?></option>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="wpcc-settings-group">

								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Calculation Result type', 'wp-calorie-calculator' ); ?></h3>
								</div>
								<div class="wpcc-settings-group-content">
									<label class="wpcc-radio">
										<input type="radio" name="wpcc-instant-result" value=""
											<?php checked( '', $instant_result ); ?>>
										<span><?php esc_attr_e( 'Send to Email', 'wp-calorie-calculator' ); ?></span>
										<span class="wpcc-tooltip">
											<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
													<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
											</svg>
											<div class="wpcc-tooltip-text">
												<?php esc_attr_e( 'Users will get the results to their Email.', 'wp-calorie-calculator' ); ?>
											</div>
										</span>
									</label>
									<label class="wpcc-radio">
										<input type="radio" name="wpcc-instant-result" value="true"
											<?php checked( 'true', $instant_result ); ?>>

										<span><?php esc_attr_e( 'Instant View', 'wp-calorie-calculator' ); ?></span>
										<span class="wpcc-tooltip">
											<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
													<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
											</svg>
											<div class="wpcc-tooltip-text">
												<?php esc_attr_e( 'The user will see the result on the page immediately.', 'wp-calorie-calculator' ); ?>
											</div>
										</span>
									</label>
									<label class="wpcc-radio disabled">
										<input type="radio" disabled>

										<span><?php esc_attr_e( 'Instant View with Email request', 'wp-calorie-calculator' ); ?></span>
										<span class="wpcc-tooltip">
											<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
													<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
											</svg>
											<div class="wpcc-tooltip-text">
												<?php esc_attr_e( 'The user will see the results on the page after entering their data.', 'wp-calorie-calculator' ); ?>
											</div>
										</span>
										<div class="wpcc-settings-pro">PRO
												<div class="wpcc-settings-pro-tooltip">
													<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
												</div>
											</div>
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group wpcc-notification-email-wrapper" style="<?php echo $instant_result ? 'display:none;' : ''; ?>">
								<div>

									<div class="wpcc-settings-group">
										<div class="wpcc-settings-group-title">
											<h3><?php esc_attr_e( 'Notification settings', 'wp-calorie-calculator' ); ?></h3>
										</div>
										<p class="wpcc-notification-email-description wpcc-settings-description">
											<?php esc_attr_e( 'Enter your email to receive notifications about new calculator users (who requested results to their email) and their email addresses', 'wp-calorie-calculator' ); ?>
										</p>
										<input type="email" name="wpcc-notification-email" placeholder="your email"
										value="<?php echo esc_attr( $notification_email ); ?>">

										<div class="wpcc-settings-group-content">
											<label class="wpcc-checkbox disabled">
												<input type="checkbox" name="wpcc_disable_admin_notification" disabled>
												<span><?php esc_attr_e( 'Disable admin notification', 'wp-calorie-calculator' ); ?></span>
												<div class="wpcc-settings-pro">PRO
													<div class="wpcc-settings-pro-tooltip">
														<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
													</div>
												</div>
											</label>
											
										</div>
									</div>

									<div class="wpcc-settings-group">
										<div class="wpcc-settings-group-title">
											<h3><?php esc_attr_e( 'User agreements', 'wp-calorie-calculator' ); ?></h3>
										</div>
										<div class="wpcc-settings-group-content">
										<label class="wpcc-checkbox">
												<input type="checkbox" name="wpcc_user_agreements" value="show" <?php checked( 'show', $wpcc_user_agreements ); ?>>
												<span><?php esc_attr_e( 'Show User agreements', 'wp-calorie-calculator' ); ?></span>
											</label>
											<div class="wpcc-user-agreements-wrapper" style="<?php echo $wpcc_user_agreements ? '' : 'display:none;'; ?>">
												<label class="wpcc-checkbox wpcc-user-agreements-wrapper">
													<input type="checkbox" name="wpcc_privacy_policy" value="show" <?php checked( 'show', $wpcc_privacy_policy ); ?>>
													<span><?php esc_attr_e( 'Privacy Policy', 'wp-calorie-calculator' ); ?></span>
												</label>
												<div class="wpcc-privacy-policy-wrapper" style="<?php echo $wpcc_privacy_policy ? '' : 'display:none;'; ?>">
													<div class="wpcc-settings-row">
														<label class="wpcc-settings-group-label mr">
															<?php esc_attr_e( 'Privacy Policy URL', 'wp-calorie-calculator' ); ?>
															<input type="text" name="wpcc_privacy_policy_url" placeholder="https://your-domain/privacy-policy" value="<?php echo esc_attr( $wpcc_privacy_policy_url ); ?>">
														</label>
														<label class="wpcc-settings-group-label">
															<?php esc_attr_e( 'Privacy Policy URL text', 'wp-calorie-calculator' ); ?>
															<input type="text" name="wpcc_privacy_policy_url_text" placeholder="Privacy Policy" value="<?php echo esc_attr( $wpcc_privacy_policy_url_text ); ?>">
														</label>
													</div>
												</div>
												<label class="wpcc-checkbox">
													<input type="checkbox" name="wpcc_terms_and_conditions" value="show" <?php checked( 'show', $wpcc_terms_and_conditions ); ?>>
													<span><?php esc_attr_e( 'Terms and Conditions', 'wp-calorie-calculator' ); ?></span>
												</label>
												<div class="wpcc-terms-wrapper" style="<?php echo $wpcc_terms_and_conditions ? '' : 'display:none;'; ?>">
													<div class="wpcc-settings-row">
														<label class="wpcc-settings-group-label mr">
															<?php esc_attr_e( 'Terms URL', 'wp-calorie-calculator' ); ?>
															<input type="text" name="wpcc_terms_and_conditions_url" placeholder="https://your-domain/terms-and-conditions" value="<?php echo esc_attr( $wpcc_terms_and_conditions_url ); ?>">
														</label>
														<label class="wpcc-settings-group-label">
															<?php esc_attr_e( 'Terms URL text', 'wp-calorie-calculator' ); ?>
															<input type="text" name="wpcc_terms_and_conditions_url_text" placeholder="Terms and Conditions" value="<?php echo esc_attr( $wpcc_terms_and_conditions_url_text ); ?>">
														</label>
													</div>
												</div>
												<label class="wpcc-settings-group-label">
													<?php esc_attr_e( 'User agreement message', 'wp-calorie-calculator' ); ?>
													<textarea class="wpcc-settings-textarea" name="wpcc_user_agreements_text" rows="2"><?php echo esc_html( $wpcc_user_agreements_text ); ?></textarea>
												</label>
												<p class="wpcc-settings-description"><?php esc_attr_e( 'If you change this value make sure to wrap the links using the {} signs: {privacy_policy} and {terms_and_conditions}.', 'wp-calorie-calculator' ); ?></p>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Labels Customization', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>

								<div class="wpcc-settings-group-content">
									<p class="wpcc-settings-description wpcc-settings-description--100">
										<?php esc_html_e( 'You can customize all texts, labels, success form submission messages, etc. in the PRO version.', 'wp-calorie-calculator' ); ?>
									</p>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Google reCAPTCHA v3', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>

								<div class="wpcc-settings-group-content">
									<label class="wpcc-checkbox">
										<input type="checkbox" disabled>
										<span><?php esc_attr_e( 'Enable Google ReCAPTCHA', 'wp-calorie-calculator' ); ?></span>
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Mailchimp', 'wp-calorie-calculator' ); ?>
									<div class="wpcc-settings-pro">PRO
										<div class="wpcc-settings-pro-tooltip">
											<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
										</div>
									</div>
								</h3>
								</div>
								<div class="wpcc-settings-group-content">
									<p class="wpcc-settings-description">
										<?php esc_attr_e( 'Enter your Mailchimp API key if you’d like to collect emails', 'wp-calorie-calculator' ); ?>
									</p>

									<div class="wpcc-settings-subgroup">
										<div class="wpcc-settings-subgroup-wrapper">
											<input class="disabled" type="text" name="wpcc_mailchimp_api_key" placeholder="Mailchimp API key" placeholder="<?php esc_attr_e( 'Your Mailchimp API key', 'wp-calorie-calculator' ); ?>">
											<button class="button wpcc-settings-mailchimp-key-button" type="button" disabled><?php esc_attr_e( 'Connect', 'wp-calorie-calculator' ); ?></button>
										</div>
									</div>

									<div class="wpcc-settings-subgroup wpcc-settings-mailchimp-lists">
										<p class="wpcc-settings-description">
											<?php esc_attr_e( 'Choose your Mailchimp email list', 'wp-calorie-calculator' ); ?></p>
											<select class="disabled"  name="wpcc_mailchimp_list_id">
												<option selected disabled><?php esc_attr_e( 'Choose Mailchimp email list', 'wp-calorie-calculator' ); ?></option>
										</select>
									</div>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Zapier', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>
								<div class="wpcc-settings-group-content">
									<p class="wpcc-settings-description wpcc-settings-description--100">
										<?php echo sprintf( esc_html__( 'In Zapier, create a zap and select %1$s as the launch application and %2$s as the launch event type. Zapier will give you the Custom Webhook URL - paste it into this field and save your settings. All data will now be sent to your Zapier account.', 'wp-calorie-calculator' ), '<i>Webhooks by Zapier</i>', '<i>Catch Hook</i>' ); ?>
										<br>
										<strong><?php esc_attr_e( 'Note: a paid Zapier plan is required!', 'wp-calorie-calculator' ); ?></strong>
									</p>

									<div class="wpcc-settings-subgroup-wrapper">
										<input class="disabled" dir="auto" type="text" placeholder="Zapier Webhook URL">
									</div>
								</div>
							</div>

							<div class="wpcc-settings-submit">
								<?php echo esc_html( submit_button( null, 'wpcc-submit', 'publish', true, array( 'id' => 'publish' ) ) ); ?>
							</div>

						</section>

						<section id="calculation-settings" class="wpcc-settings-section">
							<h2 class="wpcc-settings-section-title"><?php esc_attr_e( 'Calculation', 'wp-calorie-calculator' ); ?></h2>

							<div class="wpcc-settings-group">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Default units system', 'wp-calorie-calculator' ); ?></h3>
									<div class="wpcc-switch disabled">
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
										<div class="wpcc-switch-option" data-position="left">
											<?php esc_attr_e( 'Hide', 'wp-calorie-calculator' ); ?></div>
										<label class="wpcc-switch-toggle">
											<input disabled type="checkbox" value="" checked>
											<div class="wpcc-switch-toggle-circle"></div>
											<div class="wpcc-switch-toggle-background"></div>
										</label>
										<div class="wpcc-switch-option" data-position="right">
											<?php esc_attr_e( 'Show', 'wp-calorie-calculator' ); ?></div>
									</div>
								</div>
								<div class="wpcc-settings-group-content">
									<label class="wpcc-radio">
										<input type="radio" name="wpcc-metric-system" value="" <?php checked( '', $metric_system ); ?>>

										<span><?php esc_attr_e( 'Imperial', 'wp-calorie-calculator' ); ?></span>
										<span class="wpcc-tooltip">
										<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
												<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
										</svg>
										<div class="wpcc-tooltip-text">
											<?php esc_attr_e( 'The imperial system is a system of weights and measures that includes pounds, inches, feet etc.', 'wp-calorie-calculator' ); ?>
										</div>
									</span>
									</label>
									<label class="wpcc-radio">
										<input type="radio" name="wpcc-metric-system" value="true"
											<?php checked( 'true', $metric_system ); ?>>

										<span><?php esc_attr_e( 'Metric', 'wp-calorie-calculator' ); ?></span>
										<span class="wpcc-tooltip">
										<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
												<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
										</svg>
										<div class="wpcc-tooltip-text">
											<?php esc_attr_e( 'International decimal system of weights and measures, based on the meter for length and the kilogram for mass.', 'wp-calorie-calculator' ); ?>
										</div>
									</span>
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3>
										<?php esc_attr_e( 'Show results', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>

								<div class="wpcc-settings-group-content">

									<label class="wpcc-checkbox">
										<input type="checkbox" checked disabled>
										<span><?php esc_attr_e( 'Show Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ); ?></span>
									</label>

									<label class="wpcc-checkbox">
										<input type="checkbox" checked disabled>
										<span><?php esc_attr_e( 'Show Body Mass Index (BMI)', 'wp-calorie-calculator' ); ?></span>
									</label>

									<label class="wpcc-checkbox wpcc-show-bmi-type">
										<input type="checkbox" checked disabled>
										<span><?php esc_attr_e( 'Show BMI Type', 'wp-calorie-calculator' ); ?></span>
									</label>

									<label class="wpcc-checkbox">
										<input type="checkbox" checked disabled>
										<span><?php esc_attr_e( 'Show Macronutrient Balance', 'wp-calorie-calculator' ); ?></span>
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Choose the formula', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
									<div class="wpcc-switch disabled">
										<div class="wpcc-switch-option" data-position="left">
											<?php esc_attr_e( 'Hide', 'wp-calorie-calculator' ); ?></div>
										<label class="wpcc-switch-toggle">
											<input disabled type="checkbox" name="wpcc_can_select_formula" value="">
											<div class="wpcc-switch-toggle-circle"></div>
											<div class="wpcc-switch-toggle-background"></div>
										</label>
										<div class="wpcc-switch-option" data-position="right">
											<?php esc_attr_e( 'Show', 'wp-calorie-calculator' ); ?></div>
									</div>
								</div>
								<div class="wpcc-settings-group-content">
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_formula" value="mifflin_stjeor" checked>
										<span><?php esc_attr_e( 'Mifflin-St Jeor formula', 'wp-calorie-calculator' ); ?></span>										
									</label>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_formula" value="harris_benedict">
										<span><?php esc_attr_e( 'Harris-Benedict formula', 'wp-calorie-calculator' ); ?></span>
									</label>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_formula" value="who">
										<span><?php esc_attr_e( 'World Health Organization', 'wp-calorie-calculator' ); ?></span>									
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_html_e( 'Activity Levels', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>
								<div class="wpcc-settings-group-content wpcc-settings-table-wrapper">

									<label class="wpcc-checkbox">
										<input type="checkbox" disabled>
										<span><?php esc_attr_e( 'Hide activity', 'wp-calorie-calculator' ); ?></span>
									</label>

									<table class="wpcc-settings-table activity-levels-table">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Name', 'wp-calorie-calculator' ); ?></th>
												<th><?php esc_html_e( 'Description', 'wp-calorie-calculator' ); ?></th>
												<th><?php esc_html_e( 'Coefficient', 'wp-calorie-calculator' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php $i = 0; ?>
											<?php foreach ( $activity_levels as $key => $activity ) : ?>

												<?php if ( 0 === $i ) : ?>
													<tr class="wpcc-repeater-row-blank">
														<td><input type="text" disabled></td>
														<td><input type="text" disabled></td>
														<td><input type="number" disabled></td>
														<td>
															<button class="wpcc-repeater-row-delete" type="button" disabled>
																<img src="<?php echo esc_url( WP_CALORIE_CALCULATOR_PLUGIN_URL . '/admin/images/delete.svg' ); ?>" alt="Delete" width="20"height="20">
															</button>
														</td>
													</tr>
												<?php endif; ?>

												<tr">
													<td>
														<input type="text" placeholder="<?php echo esc_attr( $activity['name'] ); ?>" disabled>
													</td>
													<td>
														<input type="text" placeholder="<?php echo esc_attr( $activity['description'] ); ?>" disabled>
													</td>
													<td>
														<input type="number" placeholder="<?php echo esc_attr( $activity['coefficient'] ); ?>" disabled>
													</td>

													<?php if ( $i > 0 ) : ?>
														<td>
															<button class="wpcc-repeater-row-delete" type="button" disabled>
																<img src="<?php echo esc_url( WP_CALORIE_CALCULATOR_PLUGIN_URL . '/admin/images/delete.svg' ); ?>" alt="Delete" width="20" height="20">
															</button>
														</td>
													<?php else : ?>
														<td></td>
													<?php endif; ?>
												</tr>

												<?php $i++; ?>
											<?php endforeach; ?>
										</tbody>
									</table>

									<button class="wpcc-repeater-row-add button" type="button"><?php esc_html_e( 'Add Goal', 'wp-calorie-calculator' ); ?></button>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_html_e( 'Goals', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</h3>
								</div>
								<div class="wpcc-settings-group-content wpcc-settings-table-wrapper">

									<label class="wpcc-checkbox">
										<input type="checkbox" disabled>
										<span><?php esc_attr_e( 'Hide goals', 'wp-calorie-calculator' ); ?></span>
									</label>
									<table class="wpcc-settings-table goals-table">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Name', 'wp-calorie-calculator' ); ?></th>
												<th><?php esc_html_e( 'Macronutrient Ratio', 'wp-calorie-calculator' ); ?></th>
												<th><?php esc_html_e( 'Coefficient', 'wp-calorie-calculator' ); ?></th>
												<th></th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
											foreach ( $goals as $key => $goal ) :
												?>
												<?php if ( 0 === $i ) : ?>
													<tr class="wpcc-repeater-row-blank">
														<td><input type="text" disabled></td>
														<td>
															<div class="macronutrient-items">
																<label class="macronutrient-item">
																	<div>Carbs (%)</div>
																	<input type="number" disabled>
																</label>
																<label class="macronutrient-item">
																	<div>Protein (%)</div>
																	<input type="number" disabled>
																</label>
																<label class="macronutrient-item">
																	<div>Fats (%)</div>
																	<input type="number" disabled>
																</label>
															</div>
														</td>
														<td><input type="number" disabled></td>
														<td>
															<button class="wpcc-repeater-row-delete" type="button" disabled>
																<img src="<?php echo esc_url( WP_CALORIE_CALCULATOR_PLUGIN_URL . '/admin/images/delete.svg' ); ?>" alt="Delete" width="20" height="20">
															</button>
														</td>
													</tr>
												<?php endif; ?>

												<tr>
													<td>
														<input type="text" disabled placeholder="<?php echo esc_attr( $goal['name'] ); ?>">
													</td>
													<td>
														<div class="macronutrient-items">
															<label class="macronutrient-item">
																<div>Carbs (%)</div>
																<input type="number" disabled placeholder="<?php echo esc_attr( $goal['carbs'] ); ?>">
															</label>
															<label class="macronutrient-item">
																<div>Protein (%)</div>
																<input type="number" disabled placeholder="<?php echo esc_attr( $goal['protein'] ); ?>">
															</label>
															<label class="macronutrient-item">
																<div>Fats (%)</div>
																	<input type="number" disabled placeholder="<?php echo esc_attr( $goal['fats'] ); ?>">
															</label>
														</div>
													</td>
													<td>
														<input type="number" disabled placeholder="<?php echo esc_attr( $goal['coefficient'] ); ?>">
													</td>

													<?php if ( $i > 0 ) : ?>
														<td>
															<button class="wpcc-repeater-row-delete" type="button" disabled>
																<img src="<?php echo esc_url( WP_CALORIE_CALCULATOR_PLUGIN_URL . '/admin/images/delete.svg' ); ?>" alt="Delete" width="20" height="20">
															</button>
														</td>
													<?php else : ?>
														<td></td>
													<?php endif; ?>
												</tr>

												<?php $i++; ?>
											<?php endforeach; ?>
										</tbody>
									</table>

									<button class="wpcc-repeater-row-add button" type="button"><?php esc_html_e( 'Add Goal', 'wp-calorie-calculator' ); ?></button>
								</div>
							</div>

							<div class="wpcc-settings-submit">
								<?php echo esc_html( submit_button( null, 'wpcc-submit', 'publish', true, array( 'id' => 'publish' ) ) ); ?>
							</div>

						</section>

						<section id="styling-settings" class="wpcc-settings-section">
							<h2 class="wpcc-settings-section-title"><?php esc_attr_e( 'Styling', 'wp-calorie-calculator' ); ?></h2>
							<div class="wpcc-settings-group">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Color settings', 'wp-calorie-calculator' ); ?></h3>
									<div class="wpcc-switch-wrapper">
										<div class="wpcc-switch">
											
											<div class="wpcc-switch-option" data-position="left"><?php esc_attr_e( 'Color schema', 'wp-calorie-calculator' ); ?></div>
											<label class="wpcc-switch-toggle">
												<input type="checkbox" name="wpcc_use_custom_color_settings" value="true">
												<div class="wpcc-switch-toggle-circle"></div>
												<div class="wpcc-switch-toggle-background wpcc-switch-toggle-background--two-sided"></div>
											</label>
											<div class="wpcc-switch-option" data-position="right"><?php esc_attr_e( 'Custom colors', 'wp-calorie-calculator' ); ?></div>
											
										</div>
										<div class="wpcc-settings-pro wpcc-settings-pro--desktop">PRO
												<div class="wpcc-settings-pro-tooltip wpcc-settings-pro-tooltip--right">
													<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
												</div>
											</div>
									</div>
								</div>
								<div class="wpcc-settings-group-content">
									<div class="wpcc-settings-color-schema" >								
										<table class="wpcc-settings-color-table">
											<tbody>
												<tr>
													<td><label for="wpcc_schema_color_custom"><?php esc_attr_e( 'Choose color scheme', 'wp-calorie-calculator' ); ?></label></td>
													<td><input type="text" class="wpcc-color-custom" name="wpcc-primary-color" id="wpcc_schema_color_custom" value="<?php echo esc_attr( $primary_color ); ?>" data-default-color="#00B5AD" /></td>
												</tr>
											</tbody>
										</table>
									</div>							
								</div>

								<div class="wpcc-custom-colors-wrapper wpcc-settings-group block-disabled" style="display: none;">							
								<p class="wpcc-settings-description">
										<div class="wpcc-settings-pro ml-0">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									<?php esc_attr_e( 'You can customize the color scheme to your own preference in the PRO version.', 'wp-calorie-calculator' ); ?>
								</p>
									<table class="wpcc-settings-color-table">
										<tbody class="wpcc-settings-color-table-body">
											<?php foreach ( $default_colors as $layout => $colors ) : ?>
												<?php
												foreach ( $colors as $key => $color ) :
													?>
											<tr class="<?php echo esc_attr( "wpcc-color-row {$layout}  disabled" ); ?>">
												<td><label for="<?php echo esc_attr( "wpcc_{$layout}_{$key}" ); ?>"><?php echo esc_attr( $color['name'] ); ?></label></td>
												<td><input type="text" class="wpcc-color" name="<?php echo esc_attr( "wpcc_colors[$layout][{$key}]" ); ?>"
														id="<?php echo esc_attr( "wpcc_{$layout}_{$key}" ); ?>" value="<?php echo esc_attr( $color['default_color'] ); ?>"
														data-default-color="<?php echo esc_attr( $color['default_color'] ); ?>" ></td>
											</tr>
												<?php endforeach; ?>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>

							</div>

							<div class="wpcc-settings-group">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Layout style', 'wp-calorie-calculator' ); ?></h3>
								</div>
								<div class="wpcc-settings-group">
									<label class="wpcc-radio">
										<input type="radio" name="wpcc_layout_style" value="two_compact_pretty" checked>
										<span><?php esc_attr_e( 'Compact Pretty', 'wp-calorie-calculator' ); ?></span>
									</label>
									<label class="wpcc-radio disabled">
										<input type="radio" name="wpcc_layout_style" value="one_simple_plain" disabled>
										<span><?php esc_attr_e( 'Simple “Plain HTML”', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</span>							
									</label>
									<label class="wpcc-radio disabled">
										<input type="radio" name="wpcc_layout_style" value="three_extended_onescreen" disabled>
										<span><?php esc_attr_e( 'Extended', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
									</span>
									</label>							
								</div>						
							</div>

							<div class="wpcc-settings-group wpcc-settings-group-layout block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Layout settings', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
								</h3>
								</div>
								<div class="wpcc-settings-group-content wpcc-settings-row">
									<p class="wpcc-settings-description"><?php esc_attr_e( 'Form layout:', 'wp-calorie-calculator' ); ?></p>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_form_layout" value="one_screen" checked>
										<span><?php esc_attr_e( 'One-screen', 'wp-calorie-calculator' ); ?></span>
									</label>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_form_layout" value="multi_step">
										<span><?php esc_attr_e( 'Multi-step', 'wp-calorie-calculator' ); ?></span>
									</label>
								</div>
								<div class="wpcc-settings-group-content wpcc-settings-row">
									<p class="wpcc-settings-description"><?php esc_attr_e( 'Activity&Goal layout:', 'wp-calorie-calculator' ); ?>
									</p>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_layout_grid" value="slider">
										<span><?php esc_attr_e( 'Slider', 'wp-calorie-calculator' ); ?></span>
									</label>
									<label class="wpcc-radio">
										<input disabled type="radio" name="wpcc_layout_grid" value="grid"checked>
										<span><?php esc_attr_e( 'Grid', 'wp-calorie-calculator' ); ?></span>
									</label>
								</div>
							</div>

							<div class="wpcc-settings-group block-disabled">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'Base font size', 'wp-calorie-calculator' ); ?>
										<div class="wpcc-settings-pro">PRO
											<div class="wpcc-settings-pro-tooltip">
												<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
											</div>
										</div>
								</h3>
								</div>
								<div class="wpcc-settings-group-content">
									<select name="wpcc_font_size">
										<option disabled value="<?php esc_attr_e( 'Normal', 'wp-calorie-calculator' ); ?>" selected>
										<?php esc_attr_e( 'Normal', 'wp-calorie-calculator' ); ?></option>
									</select>
								</div>
							</div>

							<div class="wpcc-settings-submit">
								<?php echo esc_html( submit_button( null, 'wpcc-submit', 'publish', true, array( 'id' => 'publish' ) ) ); ?>
							</div>

						</section>				

						<section id="templates-settings" class="wpcc-settings-section block-disabled">
							<h2 class="wpcc-settings-section-title"><?php esc_attr_e( 'Email templates', 'wp-calorie-calculator' ); ?>
								<div class="wpcc-settings-pro">PRO
									<div class="wpcc-settings-pro-tooltip">
										<?php esc_attr_e( 'These features are available in the PRO version', 'wp-calorie-calculator' ); ?>
									</div>
								</div>
							</h2>
							<div class="wpcc-settings-group">
								<div class="wpcc-settings-group-title">
									<h3><?php esc_attr_e( 'User parameters', 'wp-calorie-calculator' ); ?></h3>
								</div>
								<div class="wpcc-settings-group-content">
									<p class="wpcc-settings-description">
										<?php esc_attr_e( 'Use these parameters when constructing notifications to user and administrator. Do not remove curly braces - {user_name}.', 'wp-calorie-calculator' ); ?>
									</p>
									<table class="wpcc-settings-group-text-block-table">
										<tbody>
											<tr>
												<td><?php echo '{user_name}'; ?></td>
												<td><?php esc_attr_e( "The user's name", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{user_email}'; ?></td>
												<td><?php esc_attr_e( "The user's email address", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{index_bmi}'; ?></td>
												<td><?php esc_attr_e( 'Body Mass Index (BMI)', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{bmi_class}'; ?></td>
												<td><?php esc_attr_e( 'BMI Class', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{index_bmr}'; ?></td>
												<td><?php esc_attr_e( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{result}'; ?></td>
												<td><?php esc_attr_e( 'Target calorie intake per day', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{fats}'; ?></td>
												<td><?php esc_attr_e( 'Calculated fats, g', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{protein}'; ?></td>
												<td><?php esc_attr_e( 'Calculated protein, g', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{carbs}'; ?></td>
												<td><?php esc_attr_e( 'Calculated carbs, g', 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{sex}'; ?></td>
												<td><?php esc_attr_e( "The user's sex", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{age}'; ?></td>
												<td><?php esc_attr_e( "The user's age", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{height}'; ?></td>
												<td><?php esc_attr_e( "The user's height", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{weight}'; ?></td>
												<td><?php esc_attr_e( "The user's weight", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{activity}'; ?></td>
												<td><?php esc_attr_e( "The user's activity level", 'wp-calorie-calculator' ); ?></td>
											</tr>
											<tr>
												<td><?php echo '{goal}'; ?></td>
												<td><?php esc_attr_e( "The user's desired goal", 'wp-calorie-calculator' ); ?></td>
											</tr>	
										</tbody>
									</table>							
								</div>
								<div class="wpcc-settings-group">
									<div class="wpcc-settings-group-title">
										<h3><?php esc_attr_e( 'Email template to user', 'wp-calorie-calculator' ); ?></h3>
									</div>

									<div class="wpcc-settings-group-content">
										<p class="wpcc-settings-description wpcc-settings-description--100"><?php esc_html_e( 'Fill in the From (Name/Title) and From (Email) fields - for example, WP Calorie Calculator and calculator. Leave these fields blank if you want to use the default WordPress settings or configure them on SMTP.', 'wp-calorie-calculator' ); ?></p>
										<div class="wpcc-settings-row">
											<label class="wpcc-settings-group-label mr"><?php esc_html_e( 'From (Name/Title)', 'wp-calorie-calculator' ); ?>
												<input class="disabled" type="text" name="wpcc_email_user_from_name" disabled/>
											</label>
											<label class="wpcc-settings-group-label text-left"><?php esc_html_e( 'From (Email)', 'wp-calorie-calculator' ); ?>
												<div class="wpcc-settings-wrapper" dir="ltr">
													<input class="disabled" type="text" name="wpcc_email_user_from" disabled dir="ltr"/>
													<div class="wpcc-settings-group-label--text" dir="ltr">@<?php echo isset( $current_domain ) ? esc_attr( $current_domain ) : esc_html__( 'domain.com', 'fiwy' ); ?></div>
												</div>
											</label>
										</div>

										<label class="wpcc-settings-group-label"><?php esc_html_e( 'Subject', 'wp-calorie-calculator' ); ?>
											<input class="disabled" disabled type="text" value="<?php echo esc_html( $wpcc_email_user_subject ); ?>" /></label>
										<label class="wpcc-settings-group-label"><?php esc_html_e( 'Body', 'wp-calorie-calculator' ); ?>
											<textarea rows="18" name="wpcc_template_user_email_body"
												class="wpcc-settings-textarea disabled" disabled><?php echo esc_html( $wpcc_email_user_body ); ?></textarea>
										</label>
									</div>
								</div>
								<div class="wpcc-settings-group">
									<div class="wpcc-settings-group-title">
										<h3><?php esc_attr_e( 'Notification template', 'wp-calorie-calculator' ); ?></h3>
									</div>
									<div class="wpcc-settings-group-content">
										<label class="wpcc-settings-group-label"><?php esc_html_e( 'Subject', 'wp-calorie-calculator' ); ?>
											<input class="disabled" disabled type="text" name="wpcc_template_admin_email_subject"
												value="<?php echo esc_html( $wpcc_email_admin_subject ); ?>" /></label>
										<label class="wpcc-settings-group-label"><?php esc_html_e( 'Body', 'wp-calorie-calculator' ); ?>
											<textarea rows="18" name="wpcc_template_admin_email_body"
												class="wpcc-settings-textarea disabled" disabled><?php echo esc_html( $wpcc_email_admin_body ); ?></textarea>
										</label>
									</div>
								</div>
							</div>					

						</section>

					</div>

				</div>
			</div>

			<div class="wpcc-settings-sidebar">


				<div class="wpcc-banner wpcc-banner--red">
					<div class="wpcc-banner-title"><?php esc_attr_e( "Make your website users' favorite place to be!", 'wp-calorie-calculator' ); ?></div>
					<div class="wpcc-banner-description wpcc-banner-description--bold"><?php esc_attr_e( 'Support of: ', 'wp-calorie-calculator' ); ?></div>
					<div class="wpcc-banner-description"><?php esc_attr_e( 'Mailchimp, Zapier, Google reCAPTCHA, Elementor widget and other amazing add-ons', 'wp-calorie-calculator' ); ?></div>
					<a class="wpcc-banner-button" href="https://wpcaloriecalculator.com/?visitsource=wporgfree" target="_blank"><?php esc_attr_e( 'Get it', 'wp-calorie-calculator' ); ?></a>
				</div>


			<div class="wpcc-banner wpcc-banner--purple">
				<div class="wpcc-banner-title"><?php esc_attr_e( 'Hi there!', 'wp-calorie-calculator' ); ?></div>
				<div class="wpcc-banner-description">
					<p>
						<?php
						$trustpilot_url = 'https://www.trustpilot.com/evaluate/wpcaloriecalculator.com';
						$text           = sprintf(
							// translators: Plugin urls.
							__(
								'We hope you love it, and we would really appreciate it if you would give us a <a href="%1$s" target="_blank">5 stars rating</a>.',
								'wp-calorie-calculator'
							),
							esc_url( $trustpilot_url )
						);
						echo wp_kses(
							$text,
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
					</p>	
				</div>
				<img src="<?php echo esc_url( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'admin/images/review-stars.svg' ); ?>" alt="" class="wpcc-banner-stars">
				<a href="https://www.trustpilot.com/evaluate/wpcaloriecalculator.com" class="wpcc-banner-button" target="_blank"><?php esc_html_e( 'Rate the Plugin', 'wp-calorie-calculator' ); ?></a>
			</div>
			</div>

		</div>

		<div class="wpcc-settings-footer">
		</div>
	</div>

</form>
