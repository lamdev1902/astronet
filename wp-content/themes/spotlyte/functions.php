<?php 
include(TEMPLATEPATH.'/include/menus.php');
include(TEMPLATEPATH.'/include/general-function.php');
add_theme_support( 'post-thumbnails', array('post','page','informational_posts' ) );
/* Script Admin */

add_action ( 'edited_category', 'saveCategoryFields');
function my_script() { ?>
	<style type="text/css">
		#dashboard_primary,#icl_dashboard_widget,
		#dashboard_right_now #wp-version-message,#wpfooter,#menu-comments,
		#acf-group_614af85e3eca1.acf-postbox .postbox-header,
		#acf-group_614af85e3eca1.acf-postbox .acf-field-614af86b68d45,
		#acf-group_614af85e3eca1.acf-postbox .acf-field-614af89768d46,
		.acf-field-relationship[data-name="coupon_list"] select optgroup[label="Categories"],
		input#icl_cfo,input#icl_set_duplicate{
			display:none;
		}
		#menu-pages {
			border-top:2px solid #fff !important;
			margin-top:20px !important;
		}
		#menu-posts-coupon {
			border-bottom:2px solid #fff !important;
			margin-bottom:20px !important;
		}
		#adminmenu a {
			color:#fff !important;
		}
	</style>
<?php }
add_action( 'admin_footer', 'my_script' );
function custom_style_login() {
	?>
    <style type="text/css">
		.login h1 a {
			background-image: url("<?php echo get_template_directory_uri(); ?>/assets/images/logo.svg");
			background-size: 100% auto;
			height: 60px;
			width: 200px;
		}
		.wp-social-login-provider-list img {
			max-width:100%;
		}
	</style>
<?php }
add_action( 'login_head', 'custom_style_login' );
/* add css, jquery */
function theme_mcs_scripts() {
	/* general css */
	wp_enqueue_style( 'style-awesome', get_template_directory_uri() . '/assets/fonts/font-awesome/css/all.min.css' );
	wp_enqueue_style( 'style-animate', get_template_directory_uri() . '/assets/js/animate/animate.min.css' );
	wp_enqueue_style( 'style-fonts', get_template_directory_uri() . '/assets/fonts/stylesheet.css' );
	wp_enqueue_style( 'style-slick', get_template_directory_uri() . '/assets/js/slick/slick.css' );
	wp_enqueue_style( 'style-slick-theme', get_template_directory_uri() . '/assets/js/slick/slick-theme.css' );
	wp_enqueue_style( 'style-main', get_template_directory_uri() . '/assets/css/main.css' );
	wp_enqueue_style( 'main-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'theme_mcs_scripts' );
/* register page option ACF */
if( function_exists('acf_add_options_page') ) {
	$parent = acf_add_options_page( array(
		'page_title' => 'Website Option',
		'menu_title' => 'Website Option',
		'icon_url' => 'dashicons-image-filter',
	));
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Option',
		'menu_title' 	=> 'Option',
		'parent_slug' 	=> $parent['menu_slug'],
	));
}
//add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	show_admin_bar(false);
}
/* Hide editor not use */
// add_action( 'admin_init', 'hide_editor_not_use' );
// function hide_editor_not_use() {
// 	if(isset($_GET['post']) && $_POST['post_ID']) {
// 		$post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
// 		if( !isset( $post_id ) ) return;

// 		$template_file = get_post_meta($post_id, '_wp_page_template', true);

// 		if($template_file == 'template/home.php'){
// 			remove_post_type_support('page', 'editor');
// 		}
// 	}
// }

/* load more */
function load_more_post(){
	//ajax khi nao cung phai exit() hoặc die() ở cuối function
	$page = isset($_GET['page'])?$_GET['page']:'2';
	$term = isset($_GET['term'])?$_GET['term']:'';
	 $offset = !empty($_POST['offset']) ? intval( $_POST['offset'] ) : '';
	ob_start();
	$args = array(
		'posts_per_page'	=> 2,
		'paged' => $page,
		 'offset' => $offset,
		'tax_query'			=> array(
			array(
				'taxonomy'		=> 'category',
				'field'			=> 'id',
				'terms'			=> $term
			)
		),
	);
	
	$countp = $the_query ->found_posts;
	$the_query = new WP_Query( $args );
	while ($the_query->have_posts() ) : $the_query->the_post();
	$author_id = get_post_field ('post_author', $post->ID);
	$author_name = get_the_author_meta( 'nickname' , $author_id ); 
	$author_url = get_author_posts_url( $author_id );
?>
	<div class="pd-it">
		<div class="featured image-fit">
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
		</div>
		<div class="info">
			<h4 class="type-style-overline font-15"><a href="<?php echo $terms_current->term_link;  ?>"><?php echo $terms_current->name;  ?></a></h4>
			<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $author_url;?>">BY <?php echo $author_name; ?></a><?php echo get_the_date('F d, y'); ?></h5>
		</div>
	</div>
	<?php
		endwhile;
		wp_reset_query();
	?> 
	<?php echo ob_get_clean();
	die;
}
add_action( 'wp_ajax_load_more_post', 'load_more_post' );
add_action( 'wp_ajax_nopriv_load_more_post', 'load_more_post' );

//add search to header
//THEM WIDGET VAO THEME - CHIA SE BOI HOCBAN.VN
function wpb_widgets_init() {
	register_sidebar( array(
		'name' => 'Custom Header Widget Area',
		'id' => 'custom-header-widget',
		'before_widget' => '<div class="chw-widget">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="chw-title">',
		'after_title' => '</h2>',
	) );
}
add_action( 'widgets_init', 'wpb_widgets_init' );
/* Update post id */
//  global $wpdb;
// $limit = 10;
// $offset = 0;
// $table = $wpdb->prefix . 'posts';
// $sql = "
// 	SELECT * 
// 	FROM {$table} 
// 	WHERE `post_type` = 'post' 
// 	AND ID = 7328769
// 	LIMIT %d OFFSET %d";
// $data = $wpdb->get_results( $wpdb->prepare($sql, $limit, $offset), ARRAY_A);
//var_dump($data);

function calorie_calculator($content)
{
	$content = '
		<div id="spinner"></div>
		<div id="calculator">
			<div class="container">
				<div class="wrapper">
				<div class="wrapper-header">
						<div class="header-text">
							<h2 class="title">Calorie Calculator</h2>
							<p class="sub-title"> Use this calorie calculator to find out how many calories you really need!</p>
						</div>
						<div class="header-logo">
							<img src="" style="width: 100px; height:100px" alt="" class="logo">
						</div>
					</div>
					<div class="wrapper-content">
						<div class="content-left">
							<form action="#" class="form">
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Gender</label>
										<img src="" alt="" class="label-img">
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="1" name="gender" id="male">
											<label for="male" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Male
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="gender" id="female">
											<label for="female" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Female
											</label>
										</div>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Age</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item">
											<input type="text" pattern="[0-9]+" class="" value="" name="age" id="age">
											<div class="place-holder">
												<span>Years</span>
											</div>
										</div>
										<span style="" class="age-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Weight</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item">
											<input type="text" pattern="[0-9]+" class="" value="" name="weight" id="weight">
											<div class="place-holder">
												<span>lbs</span>
											</div>
										</div>
										<span class="weight-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Height</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="height-ft">
												<input type="text" pattern="[0-9]+" class="radio-wrapper__btn" value="" name="feet" id="heightFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="height-in">
												<input type="text" pattern="[0-9]+" class="radio-wrapper__btn" value="" name="inches" id="heightIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="height-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Level of Activity</label>
									</div>
									<select name="activity" id="levelofActivity" class="select-wrapper">
										<option value="1" class="select-wrapper__option">Basal Metabolic Rate (BMR)</option>
										<option value="2" class="select-wrapper__option">Sedentary: little or no exercise</option>
										<option value="3" class="select-wrapper__option">Light: exercise 1-3 times/week</option>
										<option value="4" class="select-wrapper__option">Moderate: exercise 4-5 times/week</option>
										<option value="5" class="select-wrapper__option">Active: daily exercise or intense exercise 3-4 times/week</option>
										<option value="6" class="select-wrapper__option">Very Active: intense exercise 6-7 times/week</option>
										<option value="7" class="select-wrapper__option">Extra Active: very intense exercise daily, or physical job</option>
									</select>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Result Unit</label>
									</div>
									<select name="unit" id="resultUnit" class="select-wrapper">
										<option value="1" class="select-wrapper__option">Calories</option>
										<option value="2" class="select-wrapper__option">Kilojoules</option>
									</select>
								</div>
								<div class="column body-fat inactive">
									<div class="label-wrapper">
										<label for="" class="label">Body Fat</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item">
											<input type="text" class="" value="" name="fat" id="bodyFat">
											<div class="place-holder">
												<span>%</span>
											</div>
										</div>
										<span class="fat-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">BMR Estimation Formular</label>
									</div>
									<select name="receip" id="bmrReceip" class="select-wrapper">
										<option value="1" class="select-wrapper__option">Mifflin St Jeor</option>
										<option value="2" class="select-wrapper__option"> Revised Harris-Benedict</option>
										<option value="3" class="select-wrapper__option"> Katch-McArdle</option>
									</select>
								</div>
								<div class="action">
									<button id="btnCalculator" class="btn-primary" type="button">
										Calculate
									</button>
									<button id="btnClear" class="btn-secondary" type="button">
										Clear
									</button>
								</div>
							</form>
							<div class="disclaimer">
								This calculator is for informational purposes only. Its not a substitute for professional medical advice, diagnosis or treatment. Calculations are based on the Mifflin-St Jeor equation, the most reliable of four commonly used formulas to estimate calorie needs, according to a review in the
							</div>
						</div>
						<div class="content-right">
							<div class="result">
								<div class="result-none ">
									<img src="" alt="" class="img">
									<h4 class="">The number of calories a person uses each day depends on sex, age, weight, height and activity level.</h4>
								</div>
								<div class="flex-column result-bmr inactive">
									<p class="title">Your estimated daily calorie needs to maintain your current weight:</p>
									<p class="value"></p>
								</div>
								<div class="flex-column result-loss inactive">
									<div class="result-type">
										<p class="">Lose Weight</p>
									</div>
									<div class="goals">
										<p class="no-loss inactive">You probably do not need to lose weight!</p>
									</div>
								</div>
								<div class="flex-column result-gain inactive">
									<div class="result-type">
										<p class="">Gain Weight</p>
									</div>
									<div class="goals">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	$a = "";
	return $content;
}
add_shortcode('calculator_form','calorie_calculator');

add_action('init', 'my_register_styles');

function my_register_styles() {
    wp_register_style( 'age', get_template_directory_uri() . '/age.css' );
    wp_register_style( 'bodyfat', get_template_directory_uri() . '/body-fat.css' );
    wp_register_style( 'bmr', get_template_directory_uri() . '/bmr.css' );
}

function age_calculator($content)
{
	wp_enqueue_style( 'age' );
	$content .= '
		<div id="spinner"></div>
		<div id="calculator">
			<div class="container">
				<div class="wrapper">
					<div class="wrapper-content age">
						<div class="content-top inactive">
							<div class="">
								<div class="title">
									<h2>Result</h2>
								</div>
								<div class="result">

								</div>
							</div>
						</div>
						<div class="content-bottom">
							<form action="#" class="form age-calculate">
								<div class="column">
									<div class="text-wrapper">

										<div class="text-wrapper__item">
											<input type="hidden" class="" value="1990-01-01" name="dayOfBirth" id="dayOfBirth">
										</div>
										<div class="options">
											<div class="label-wrapper">
												<label for="" class="label">Date of Birth</label>
											</div>
											<div class="day-option age-option">
												<select class="dateMonthInput" name="mon-birth">
													<option value="1" selected>Jan</option>
													<option value="2">Feb</option>
													<option value="3">Mar</option>
													<option value="4">Apr</option>
													<option value="5">May</option>
													<option value="6">Jun</option>
													<option value="7">Jul</option>
													<option value="8">Aug</option>
													<option value="9">Sep</option>
													<option value="10">Oct</option>
													<option value="11">Nov</option>
													<option value="12">Dec</option>
												</select>
											</div>
											<div class="mon-option age-option">
												<select class="dayDateInput" name="date-birth">
												</select>
											</div>
											<div class="year-option age-option">
												<input type="text" value="1990" class="" name="year-birth">
											</div>
										</div>
										<span class="birth-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="text-wrapper">
										<div class="text-wrapper__item">
											<input type="hidden" class="" value="" name="ageOfTheDate" id="birth">
										</div>
										<div class="options">
											<div class="label-wrapper">
												<label for="" class="label">Age at the Date</label>
											</div>
											<div class="day-option age-option">
												<select class="ageMonthInput" name="mon-age">
													<option value="1">Jan</option>
													<option value="2">Feb</option>
													<option value="3">Mar</option>
													<option value="4">Apr</option>
													<option value="5">May</option>
													<option value="6">Jun</option>
													<option value="7">Jul</option>
													<option value="8">Aug</option>
													<option value="9">Sep</option>
													<option value="10">Oct</option>
													<option value="11">Nov</option>
													<option value="12">Dec</option>
												</select>
											</div>
											<div class="mon-option age-option">
												<select class="ageDateInput" name="date-age">
												</select>
											</div>
											<div class="year-option age-option">
												<input type="text" class="" name="year-age">
											</div>
										</div>
										<span class="ageof-error error"></span>
									</div>
								</div>
								<div class="action">
									<button id="btnAge" class="btn-primary" type="button">
										Calculate
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $content;
}
add_shortcode('age_calculate','age_calculator');


function body_fat_calculator($content)
{
	wp_enqueue_style( 'bodyfat' );
	
	$content .= '
		<div id="spinner"></div>
		<div id="calculate">
			<div class="container">
				<div class="calculate--wrapper">
					<div class="calculate--wrapper__content">
						<div class="content-left">
							<form action="#" class="form bodyfat-calculate">
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Gender</label>
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" checked class="radio-wrapper__btn" value="1" name="info[gender]" id="male">
											<label for="male" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Male
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="info[gender]" id="female">
											<label for="female" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Female
											</label>
										</div>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Age</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[age]" id="age">
												<div class="place-holder">
													<span>Years</span>
												</div>
											</div>
											<span style="" class="age-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Weight</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[weight]" id="weight">
												<div class="place-holder">
													<span>Pounds</span>
												</div>
											</div>
											<span style="" class="weight-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Height</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="height-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][feet]" id="heightFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="height-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][inches]" id="heightIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="height-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Neck</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="neck-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[neck][feet]" id="neckFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="neck-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[neck][inches]" id="neckIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="neck-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Waist</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="waist-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[waist][feet]" id="waistFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="waist-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[waist][inches]" id="waistIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="waist-error error"></span>
									</div>
								</div>
								<div class="column hip inactive">
									<div class="label-wrapper">
										<label for="" class="label">Hip</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="hip-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[hip][feet]" id="hipFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="hip-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[hip][inches]" id="hipIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="hip-error error"></span>
									</div>
								</div>
								<div class="action">
									<button id="btnBodyFat" disabled="disabled" class="btn-primary" type="button">
										Calculate
									</button>
									<button id="btnClear" class="btn-secondary" type="button">
										Clear
									</button>
								</div>
							</form>
						</div>
						<div class="content-right inactive">
							<div class="">
								<div class="title">
									<h2>Result</h2>
								</div>
								<div class="main-result">
									
								</div>
								<div class="result">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $content;
}

add_shortcode('body_fat','body_fat_calculator');

function bmi_calculator($content)
{
	wp_enqueue_style( 'bodyfat' );
	
	$content .= '
		<div id="spinner"></div>
		<div id="calculate">
			<div class="container">
				<div class="calculate--wrapper">
					<div class="calculate--wrapper__content">
						<div class="content-left">
							<form action="#" class="form bmi-calculate">
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Gender</label>
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" checked class="radio-wrapper__btn" value="1" name="info[gender]" id="male">
											<label for="male" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Male
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="info[gender]" id="female">
											<label for="female" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Female
											</label>
										</div>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Age</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[age]" id="age">
												<div class="place-holder">
													<span>Years</span>
												</div>
											</div>
											<span style="" class="age-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Weight</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[weight]" id="weight">
												<div class="place-holder">
													<span>Pounds</span>
												</div>
											</div>
											<span style="" class="weight-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Height</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="height-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][feet]" id="heightFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="height-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][inches]" id="heightIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="height-error error"></span>
									</div>
								</div>
								<div class="action">
									<button id="btnBmi" disabled="disabled" class="btn-primary" type="button">
										Calculate
									</button>
									<button id="btnClear" class="btn-secondary" type="button">
										Clear
									</button>
								</div>
							</form>
						</div>
						<div class="content-right inactive">
							<div class="">
								<div class="title">
									<h2>Result</h2>
								</div>
								<div class="main-result">
									
								</div>
								<div class="result">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $content;
}

add_shortcode('bmi_calculate','bmi_calculator');

function gender_calculator($content)
{
	$content .= '
        <div id="spinner"></div>
        <div id="calculator">
            <div class="container">
                <div class="wrapper">
                    <div class="wrapper-content gender">
                        <div class="content-top inactive">
                            <div class="">
                                <div class="title">
                                    <h2>Result</h2>
                                </div>
                                <div class="result">

                                </div>
                            </div>
                        </div>
                        <div class="content-bottom">
                            <form action="#" class="form gender-calculate">
                                <div class="form-row">
                                    <div class="label">Your Due Date</div>
                                    <div class="date">
                                        <input name="dd" id="dueDatepicker">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="label">Your Birth Date</div>
                                    <div class="date">
                                        <input name="dob" readonly id="dobDatepicker">
                                    </div>
                                </div>
                                <div class="action">
                                    <button id="btnGender" class="btn-primary"  type="button">
                                        Calculate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ';
    return $content;

}
add_shortcode('gender_calculate','gender_calculator');

function bmr_calculator($content)
{
	wp_enqueue_style( 'bodyfat' );
	
	$content .= '
		<div id="spinner"></div>
		<div id="calculate">
			<div class="container">
				<div class="calculate--wrapper">
					<div class="calculate--wrapper__content">
						<div class="content-left">
							<form action="#" class="form bmr-calculate">
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Gender</label>
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" checked class="radio-wrapper__btn" value="1" name="info[gender]" id="male">
											<label for="male" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Male
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="info[gender]" id="female">
											<label for="female" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Female
											</label>
										</div>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Age</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[age]" id="age">
												<div class="place-holder">
													<span>Years</span>
												</div>
											</div>
											<span style="" class="age-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Weight</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[weight]" id="weight">
												<div class="place-holder">
													<span>Pounds</span>
												</div>
											</div>
											<span style="" class="weight-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Height</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="height-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][feet]" id="heightFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="height-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][inches]" id="heightIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="height-error error"></span>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Result Unit</label>
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" checked class="radio-wrapper__btn" value="1" name="info[unit]" id="calo">
											<label for="calo" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Calorie
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="info[unit]" id="kilo">
											<label for="kilo" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Kilojoules
											</label>
										</div>
									</div>
								</div>
								<div class="column body-fat inactive">
									<div class="label-wrapper">
										<label for="" class="label">Body Fat</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item only-one">
											<input type="text" class="" value="" name="fat" id="bodyFat">
											<div class="place-holder">
												<span>%</span>
											</div>
										</div>
										<span class="fat-error error"></span>
									</div>
								</div>
								<div class="column flex-column">
									<div class="label-wrapper">
										<label for="" class="label">BMR Estimation Formular</label>
									</div>
									<select name="receip" id="bmrReceip" class="select-wrapper">
										<option value="1" class="select-wrapper__option">Mifflin St Jeor</option>
										<option value="2" class="select-wrapper__option"> Revised Harris-Benedict</option>
										<option value="3" class="select-wrapper__option"> Katch-McArdle</option>
									</select>
								</div>
								<div class="action">
									<button id="btnBmr" disabled="disabled" class="btn-primary" type="button">
										Calculate
									</button>
									<button id="btnClear" class="btn-secondary" type="button">
										Clear
									</button>
								</div>
							</form>
						</div>
						<div class="content-right inactive">
							<div class="">
								<div class="title">
									<h2>Result</h2>
								</div>
								<div class="main-result">
									
								</div>
								<div class="result">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $content;
}

add_shortcode('bmr_calculate','bmr_calculator');

function ideal_weight_calculator($content)
{
	wp_enqueue_style( 'bodyfat' );
	
	$content .= '
		<div id="spinner"></div>
		<div id="calculate">
			<div class="container">
				<div class="calculate--wrapper">
					<div class="calculate--wrapper__content">
						<div class="content-left">
							<form action="#" class="form ideal-weight-calculate">
								<div class="column">
									<div class="label-wrapper img">
										<label for="male" class="label">Gender</label>
									</div>
									<div class="radio-wrapper">
										<div class="radio-wrapper__item">
											<input type="radio" checked class="radio-wrapper__btn" value="1" name="info[gender]" id="male">
											<label for="male" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Male
											</label>
										</div>
										<div class="radio-wrapper__item">
											<input type="radio" class="radio-wrapper__btn" value="2" name="info[gender]" id="female">
											<label for="female" class="radio-wrapper__label">
												<span class="radio-visibility"></span>
												Female
											</label>
										</div>
									</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="male" class="label">Age</label>
									</div>
									<div class="text-wrapper">
											<div class="text-wrapper__item only-one">
												<input type="text"  class="" value="" name="info[age]" id="age">
												<div class="place-holder">
													<span>Years</span>
												</div>
											</div>
											<span style="" class="age-error error"></span>
										</div>
								</div>
								<div class="column">
									<div class="label-wrapper">
										<label for="" class="label">Height</label>
									</div>
									<div class="text-wrapper">
										<div class="text-wrapper__item us">
											<div class="height-ft">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][feet]" id="heightFt">
												<div class="place-holder">
													<span>ft</span>
												</div>
											</div>
											<div class="height-in">
												<input type="text" class="radio-wrapper__btn" value="" name="info[height][inches]" id="heightIn">
												<div class="place-holder">
													<span>in</span>
												</div>
											</div>
										</div>
										<span class="height-error error"></span>
									</div>
								</div>
								<div class="action">
									<button id="btnIdealWeight" disabled="disabled" class="btn-primary" type="button">
										Calculate
									</button>
									<button id="btnClear" class="btn-secondary" type="button">
										Clear
									</button>
								</div>
							</form>
						</div>
						<div class="content-right inactive">
							<div class="">
								<div class="title">
									<h2>Result</h2>
								</div>
								<div class="main-result">
									
								</div>
								<div class="result">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	';
	return $content;
}

add_shortcode('ideal_weight_calculate','ideal_weight_calculator');
