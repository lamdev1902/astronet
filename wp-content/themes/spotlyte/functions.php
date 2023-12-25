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