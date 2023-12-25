<?php 
function year_shortcode() {
  $year = date('Y');
  return $year;
}

add_filter( 'single_post_title', 'my_shortcode_title' );
add_filter( 'the_title', 'my_shortcode_title' );
add_filter( 'wp_title', 'my_shortcode_title' );
function my_shortcode_title( $title ){
	$title = strip_tags($title);
    return do_shortcode( $title );
}
add_filter( 'pre_get_document_title', function( $title ){
    // Make any changes here
    return do_shortcode( $title );
}, 999, 1 );

add_shortcode('Year', 'year_shortcode');
add_shortcode('year', 'year_shortcode');

function month_shortcode() {
  $month = date('F');
  return $month;
}
add_shortcode('month', 'month_shortcode');
add_shortcode('Month', 'month_shortcode');
?>