<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://belovdigital.agency
 * @since      1.0.0
 *
 * @package    WP_Calorie_Calculator
 * @subpackage WP_Calorie_Calculator/public/partials
 *
 * @global array $atts (input data)
 */

// Shortcode input data.
$wpcc       = new WP_Calorie_Calculator();
$title_hide = get_option( 'wpcc-title-hide', '' );
$title_show = get_option( 'wpcc-title-show' );

if ( ! $title_show && $title_hide ) {
	$title_show = false;
}

$title_text      = get_option( 'wpcc-title-text', __( 'CALCULATE YOUR OPTIMAL CALORIES', 'wp-calorie-calculator' ) );
$metric_system   = get_option( 'wpcc-metric-system', '' );
$instant_result  = get_option( 'wpcc-instant-result', '' );
$primary_color   = get_option( 'wpcc-primary-color', '#325878' );
$secondary_color = get_option( 'wpcc-secondary-color', '#4989BE' );
$default_colors  = $wpcc->get_calculator_default_colors( $primary_color );
$wpcc_goals      = $wpcc->get_calculator_default_goals();
$wpcc_activity   = $wpcc->get_calculator_activity();

foreach ( $default_colors['two_compact_pretty'] as $name => $color ) {
	$colors_for_css_variables[ str_replace( '_', '-', $name ) ] = $color['default_color'];
}

// Privacy Policy Block.
$wpcc_user_agreements      = get_option( 'wpcc_user_agreements', false );
$wpcc_user_agreements_text = '';

if ( $wpcc_user_agreements ) :
	$wpcc_privacy_policy_url      = get_option( 'wpcc_privacy_policy_url', '#' );
	$wpcc_privacy_policy_url_text = get_option( 'wpcc_privacy_policy_url_text', __( 'Privacy Policy', 'wp-calorie-calculator' ) );

	$privacy_policy_link = '<a class="wpcc-result-link" href="' . esc_url( $wpcc_privacy_policy_url ) . '" target="_blank">' . esc_html( $wpcc_privacy_policy_url_text ) . '</a>';

	$wpcc_terms_and_conditions_url      = get_option( 'wpcc_terms_and_conditions_url', '' );
	$wpcc_terms_and_conditions_url_text = get_option( 'wpcc_terms_and_conditions_url_text', __( 'Terms and Conditions', 'wp-calorie-calculator' ) );

	$terms_and_conditions_link = '<a class="wpcc-result-link" href="' . esc_url( $wpcc_terms_and_conditions_url ) . '" target="_blank">' . esc_html( $wpcc_terms_and_conditions_url_text ) . '</a>';

	$wpcc_user_agreements_text = get_option( 'wpcc_user_agreements_text', __( 'By clicking submit button you agree to our {privacy_policy} and {terms_and_conditions}.', 'wp-calorie-calculator' ) );

	$wpcc_user_agreements_text = str_replace( '{privacy_policy}', $privacy_policy_link, $wpcc_user_agreements_text );
	$wpcc_user_agreements_text = str_replace( '{terms_and_conditions}', $terms_and_conditions_link, $wpcc_user_agreements_text );
endif;
?>

<div 
	class="wp-calorie-calculator wpcc-two-compact-pretty" 
	data-colors="<?php echo esc_attr( wp_json_encode( $colors_for_css_variables, JSON_UNESCAPED_UNICODE ) ); ?>"
	data-goals="<?php echo esc_attr( wp_json_encode( $wpcc_goals, JSON_UNESCAPED_UNICODE ) ); ?>"
	data-font-size = "16px"
	>

<div class="wpcc-title" style="<?php echo $title_show ? '' : 'display: none'; ?>"><?php echo esc_attr( $title_text ); ?></div>

	<form>

		<div class="wpcc-switch-wrapper">
			<div class="wpcc-switch">
				<div class="wpcc-switch-option" data-position="left"><?php esc_attr_e( 'Imperial', 'wp-calorie-calculator' ); ?></div>
				<label class="wpcc-switch-toggle">
					<input type="checkbox" name="wpcc-metric-system" value="true" <?php checked( 'true', $metric_system ); ?>>
					<div class="wpcc-switch-toggle-circle"></div>
					<div class="wpcc-switch-toggle-background wpcc-switch-toggle-background--two-sided"></div>				
				</label>
				<div class="wpcc-switch-option" data-position="right"><?php esc_attr_e( 'Metric', 'wp-calorie-calculator' ); ?></div>
			</div>
		</div>

		<div class="wpcc-group">
			<div class="wpcc-group-title"><?php esc_attr_e( 'Basic Information', 'wp-calorie-calculator' ); ?></div>

			<div id="wpcc_metric" class="wpcc-row wpcc-row-basic wpcc-metric<?php echo ! $metric_system ? ' imperial' : ''; ?>">

				<div class="wpcc-select-wrapper wpcc-sex">
					<input class="wpcc-sex-placeholder" type="hidden" value="<?php esc_attr_e( 'm / f', 'wp-calorie-calculator' ); ?>"/>					
					<label for="wpcc-gender"><?php esc_attr_e( 'Sex', 'wp-calorie-calculator' ); ?></label>
					<select class="wpcc-select select-sex" name="wpcc-gender" id="wpcc-gender" value="false">
						<option value="male"><?php esc_attr_e( 'Male', 'wp-calorie-calculator' ); ?></option>
						<option value="female"><?php esc_attr_e( 'Female', 'wp-calorie-calculator' ); ?></option>
					</select>
				</div>

				<div class="wpcc-input-wrapper wpcc-age">
					<label class="wpcc-group-label" for="wpcc-age"><?php esc_attr_e( 'Age', 'wp-calorie-calculator' ); ?></label>

					<div class="wpcc-input">
						<input type="number" class="wpcc-need-validate" id="wpcc-age" name="wpcc-age" placeholder="&nbsp;">
						<span class="wpcc-input-placeholder"><?php esc_attr_e( 'years', 'wp-calorie-calculator' ); ?></span>
					</div>

				</div>

				<div class="wpcc-input-wrapper wpcc-weight">
					<label class="wpcc-group-label" for="wpcc-weight"><?php esc_attr_e( 'Weight', 'wp-calorie-calculator' ); ?></label>
					<div class="wpcc-input">
						<input type="number" class="wpcc-need-validate" id="wpcc-weight" name="wpcc-weight" step="0.1" required placeholder="&nbsp;">
						<span class="wpcc-input-placeholder"><?php echo $metric_system ? esc_attr__( 'kg', 'wp-calorie-calculator' ) : esc_attr__( 'lbs', 'wp-calorie-calculator' ); ?></span>
					</div>
				</div>

				<div class="wpcc-input-wrapper wpcc-height">
					<label class="wpcc-group-label" for="wpcc-height"><?php esc_attr_e( 'Height', 'wp-calorie-calculator' ); ?></label>

					<div class="wpcc-row">					
						<div class="wpcc-input">
							<input type="number" class="wpcc-need-validate" id="wpcc-height" name="wpcc-height" step="0.1" required placeholder="&nbsp;">
							<span class="wpcc-input-placeholder"><?php echo $metric_system ? esc_attr__( 'cm', 'wp-calorie-calculator' ) : esc_attr__( 'ft', 'wp-calorie-calculator' ); ?></span>
						</div>

						<div class="wpcc-input" style="<?php echo $metric_system ? 'display:none;' : ''; ?>">
							<input type="number" id="wpcc-height-2" name="wpcc-height-2" step="0.1" required placeholder="&nbsp;">
							<span class="wpcc-input-placeholder"><?php esc_attr_e( 'in', 'wp-calorie-calculator' ); ?></span>
						</div>
					</div>
				</div>			

			</div>
		</div>

		<div class="wpcc-row">

			<div class="wpcc-group wpcc-group-goal">
				<label class="wpcc-group-title" for="wpcc-goal"><?php esc_attr_e( 'Goal', 'wp-calorie-calculator' ); ?></label>
				<select class="wpcc-select select-value" name="wpcc-goal" id="wpcc-goal">
					<?php $i = 0; foreach ( $wpcc_goals as $key => $goal ) : ?>
						<option value="<?php echo esc_attr( $goal['name'] . '%-%' . $goal['coefficient'] ); ?>" <?php selected( 0, $i ); ?>><?php echo esc_attr( $goal['name'] ); ?></option>
						<?php
						$i++;
					endforeach;
					?>
				</select>
			</div>

			<div class="wpcc-group wpcc-group-activity">
				<div class="wpcc-group-title"><?php esc_html_e( 'Activity Level', 'wp-calorie-calculator' ); ?></div>

				<div class="wpcc-row">
					<?php
					$i = 0;
					foreach ( $wpcc_activity as $key => $activity ) :
						?>
						<label class="wpcc-radio">
							<input type="radio" name="wpcc-activity" value="<?php echo esc_attr( $activity['name'] . '%-%' . $activity['coefficient'] ); ?>" <?php checked( 0, $i ); ?> />
							<span class="wpcc-radio-title"><?php echo esc_attr( $activity['name'] ); ?></span>
							<span class="wpcc-tooltip">
								<svg class="wpcc-tooltip-icon" style="width:16px;height:16px">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M7.99996 15.3334C12.05 15.3334 15.3333 12.0501 15.3333 8.00002C15.3333 3.94993 12.05 0.666687 7.99996 0.666687C3.94987 0.666687 0.666626 3.94993 0.666626 8.00002C0.666626 12.0501 3.94987 15.3334 7.99996 15.3334ZM7.88538 9.7396C7.31246 9.7396 7.026 9.38153 7.026 8.91278C7.026 8.21617 7.37105 7.76695 8.17834 7.15497C8.19291 7.14388 8.20736 7.13289 8.2217 7.12198C8.77152 6.70372 9.15491 6.41207 9.15491 5.88544C9.15491 5.29299 8.60152 4.94794 7.98303 4.94794C7.47522 4.94794 7.08459 5.13674 6.7786 5.53387C6.55725 5.76174 6.38147 5.88544 6.08199 5.88544C5.5872 5.88544 5.33329 5.54038 5.33329 5.14325C5.33329 4.7396 5.56116 4.32945 5.91923 4.01695C6.401 3.60028 7.16923 3.33335 8.18485 3.33335C9.99475 3.33335 11.2643 4.22528 11.2643 5.76174C11.2643 6.88153 10.5937 7.4219 9.80595 7.96877C9.27209 8.3594 9.01819 8.58726 8.81637 9.0495L8.81583 9.05044C8.59474 9.4406 8.42531 9.7396 7.88538 9.7396ZM7.87235 12.513C7.24735 12.513 6.73303 12.1094 6.73303 11.4844C6.73303 10.8594 7.24735 10.4557 7.87235 10.4557C8.49735 10.4557 9.00517 10.8594 9.00517 11.4844C9.00517 12.1094 8.49735 12.513 7.87235 12.513Z" fill="currentColor"/>
								</svg>
								<div class="wpcc-tooltip-text">
									<?php echo esc_attr( $activity['description'] ); ?>
								</div>
							</span>
						</label>
						<?php
						$i++;
					endforeach;
					?>
				</div>
			</div>
	</div>

	</form>

	<div class="wpcc-result">
		<div class="wpcc-result-icon">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 19.1668C15.0626 19.1668 19.1666 15.0628 19.1666 10.0002C19.1666 4.93755 15.0626 0.833496 9.99998 0.833496C4.93737 0.833496 0.833313 4.93755 0.833313 10.0002C0.833313 15.0628 4.93737 19.1668 9.99998 19.1668ZM15.0955 7.70874C15.3488 7.42586 15.3248 6.99122 15.0419 6.73795C14.759 6.48468 14.3244 6.5087 14.0711 6.79158L10.9827 10.2412C10.3569 10.9402 9.93544 11.4084 9.57512 11.7113C9.23187 11.9998 9.03026 12.0627 8.85415 12.0627C8.67803 12.0627 8.47642 11.9998 8.13317 11.7113C7.77285 11.4084 7.3514 10.9402 6.7256 10.2412L5.92886 9.35125C5.67559 9.06836 5.24095 9.04435 4.95807 9.29762C4.67518 9.55089 4.65117 9.98552 4.90444 10.2684L5.73522 11.1963C6.31808 11.8474 6.80424 12.3905 7.24847 12.7638C7.71839 13.1588 8.22524 13.4377 8.85415 13.4377C9.48305 13.4377 9.98991 13.1588 10.4598 12.7638C10.904 12.3905 11.3902 11.8474 11.9731 11.1963L15.0955 7.70874Z" fill="currentColor"/>
			</svg>
		</div>
		<?php if ( $instant_result ) : ?>
			<div class="wpcc-result-title"><?php esc_attr_e( 'Your results:', 'wp-calorie-calculator' ); ?></div>

			<div class="wpcc-row">
				<div class="wpcc-row-group wpcc-result-calorie">
					<div class="wpcc-result-subtitle"><?php esc_attr_e( 'Target calorie intake per day:', 'wp-calorie-calculator' ); ?></div>		
					<div class="wpcc-result-data">
						<span class="wpcc-result-data-count wp-calorie-calculator-result-count">0</span>			
					</div>
				</div>
			</div>
		<?php else : ?>
			<div class="wpcc-result-title"><?php esc_attr_e( 'Enter your email for results:', 'wp-calorie-calculator' ); ?></div>

			<form class="wpcc-result-form" method="post" action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
				<div class="wpcc-result-form-wrapper">
					<?php wp_nonce_field( 'wpcc-nonce', 'wpcc_nonce' ); ?>
					<input value="" class="wpcc-result-form-email" type="email" name="email" placeholder="<?php esc_attr_e( 'my@email.com', 'wp-calorie-calculator' ); ?>" required>
				</div>
				<button class="wpcc-result-form-submit" type="submit">
				<?php echo esc_attr( 'Calculate now', 'wp-calorie-calculator' ); ?></button>

				<?php if ( $wpcc_user_agreements ) : ?>
					<div class="wpcc-result-form-user-agreement">
						<label id="wpcc_user_acceptance" class="wpcc-checkbox wpcc-checkbox--privacy">
							<input type="checkbox" name="wpcc_user_acceptance">
							<span class="wpcc-checkbox-text"><?php echo wp_kses_post( $wpcc_user_agreements_text ); ?>
								<span class="wpcc-checkbox-check">
									<svg width="12" height="9" viewBox="0 0 12 9" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M6.15997 5.77291C5.79355 6.17659 5.1713 6.21239 4.76101 5.85338L2.47264 3.85105C1.98889 3.42776 1.23367 3.59248 0.970114 4.17876C0.799325 4.55868 0.893991 5.00544 1.20419 5.28344L4.7642 8.47391C5.172 8.83937 5.79788 8.80889 6.16822 8.40552L11.7274 2.35056C11.9737 2.08224 12.0366 1.69299 11.8873 1.36076C11.618 0.761862 10.8229 0.635754 10.3816 1.12196L6.15997 5.77291Z" fill="currentColor" />
									</svg>
								</span>
						</span>
						</label>
					</div>
				<?php endif; ?>
			</form>

			<div class="wpcc-result-form-notice"></div>
		<?php endif; ?>

		<div class="wpcc-powered">
			<a href="https://wpcaloriecalculator.com/?visitsource=poweredby" target="_blank">
				<?php esc_attr_e( 'Get WP Calorie Calculator', 'wp-calorie-calculator' ); ?>
				<svg width="16px" height="16px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15.0377 6.34326L13.6268 7.76078L16.897 11.0157L3.29199 11.0294L3.294 13.0294L16.8618 13.0158L13.6466 16.246L15.0641 17.6569L20.7078 11.9869L15.0377 6.34326Z" fill="currentColor" />
				</svg>
			</a>
		</div>
	</div>

	<?php
	if ( current_user_can( 'administrator' ) ) :
		?>
		<div class="wpcc-edit-link-wrapper">
			<a class="wpcc-edit-link" target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wp-calorie-calculator' ) ); ?>"><?php esc_attr_e( 'Edit Calculator', 'wp-calorie-calculator' ); ?></a>
			<span class="wpcc-tooltip">
				<svg class="wpcc-tooltip-icon" style="width:20px;height:20px">
					<use xlink:href="<?php echo esc_attr( WP_CALORIE_CALCULATOR_PLUGIN_URL . 'public/images/help.svg#help' ); ?>"></use>
				</svg>
				<div class="wpcc-tooltip-text">
					<?php esc_attr_e( 'You can see it because you’re logged in as an administrator', 'wp-calorie-calculator' ); ?>
				</div>
			</span>
		</div>
		<?php
	endif;
	?>
</div>
