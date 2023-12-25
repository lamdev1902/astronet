<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
function wpsfi_placeholder_image( $default = true ){
    if( $default ){
        return WPSFI_URL.'admin/assets/images/placeholder.png';
    }else{
        return WPSFI_URL.'admin/assets/images/placeholder-thumb.png';
    }  
}
function wpsfi_get_featured_image_url( $termID, $placeholderSize = true, $size = false ){
    $imageID = get_term_meta( $termID, 'wpsfi_tax_image_id', true );
    if ( $imageID ) {
        $image_url = wp_get_attachment_thumb_url( $imageID );
        if( $size ){
            $image_url = wp_get_attachment_image_url(  $imageID, $size );
        }
    } else {
        $image_url = wpsfi_placeholder_image( $placeholderSize );
    }
    $image_url    = str_replace( ' ', '%20', $image_url );
    return $image_url;
}
function wpsfi_display_image( $termID, $size = "medium", $class = '', $width = '', $height ='', $placeholderSize = true  ){
    $imageID = get_term_meta( $termID, 'wpsfi_tax_image_id', true );
    $image   = '<img class="wpsfi-featured-image" src="'.wpsfi_placeholder_image( $placeholderSize ).'" alt="placeholder"';
    if( $imageID ){
        if( $width && $height ){
            $image = wp_get_attachment_image( $imageID, array( $width, $height ), false, array( "class" => 'wpsfi-featured-image '.$class ) );
        }else{
            $image =  wp_get_attachment_image( $imageID, $size, false, array( "class" => 'wpsfi-featured-image '.$class ) );
        } 
    }
    return $image;  
}
function wpsfi_get_registered_taxonomy(){
    $taxonomies = array();
    $args = array(
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false
    );
    $post_types = get_post_types( $args );
    if( !empty( $post_types ) ){
        foreach( $post_types as $key => $value ){
            // Exclude Woocommerce taxonomy
            if( $key == 'product' ){
                continue;
            }
            $object_taxonomies = get_object_taxonomies( $key, 'objects' );
            foreach( $object_taxonomies as $tax => $obj_tax ){
                if( !$obj_tax->show_ui ){
                    continue;
                }
                $taxonomies[$tax] = $obj_tax->label;
            }
        }
    }
    return $taxonomies;
}
function wpsfi_animate_classes( $class_section = '' ){
    $entrances_list =array( 'bouncing_entrances', 'fading_entrances', 'rotating_entrances', 'sliding_entrances', 'zoom_entances', 'specials' );
    // Bouncing Entrances
    $bouncing_entrances = array(
        'bounceIn',
        'bounceInDown',
        'bounceInLeft',
        'bounceInRight',
        'bounceInUp'
    );
    // Fading Entrances
    $fading_entrances = array(
        'fadeIn',
        'fadeInDown',
        'fadeInDownBig',
        'fadeInLeft',
        'fadeInLeftBig',
        'fadeInRight',
        'fadeInRightBig',
        'fadeInUp',
        'fadeInUpBig',
    );
    // Rotating Entrances
    $rotating_entrances = array(
        'rotateIn',
        'rotateInDownLeft',
        'rotateInDownRight',
        'rotateInUpLeft',
        'rotateInUpRight',
    );
    // Sliding Entrances
    $sliding_entrances = array(
        'slideInUp',
        'slideInDown',
        'slideInLeft',
        'slideInRight'
    );
    // Zoom Entrances
    $zoom_entances = array(
        'zoomIn',
        'zoomInDown',
        'zoomInLeft',
        'zoomInRight',
        'zoomInUp'
    );
    // Specials
    $specials = array(
        'jackInTheBox',
        'rollIn'
    );
    if( empty( $class_section ) ){
        $classes = array_merge( $bouncing_entrances, $fading_entrances, $rotating_entrances, $sliding_entrances, $zoom_entances, $specials ); 
    }else{
        if( !in_array( $class_section, $entrances_list ) ){
            $classes = array();
        }else{
            $classes = $$class_section;
        }
    }
    return $classes;
}

function wpsfi_excluded_post_type_list(){
    return apply_filters( 'wpsfi_excluded_post_type', array(
        'product', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon'
    ) );
}
function wpsfi_post_type_list(){
    $post_type_list = array();
    $post_types     = get_post_types( array( '_builtin' => false ), 'object' );
    if( !empty( $post_types ) ){
        foreach ($post_types as $key => $value ) {
            if( in_array( $key, wpsfi_excluded_post_type_list() ) ){
                continue;
            }
            $post_type_list[$key] = $value->label;
        }
    }
    return $post_type_list;
}
// Enable all Custom Post Type display Featured Image
add_action( 'admin_head', function(){
    global $wpdb;
    $wpsfi_post_type = $wpdb->get_var( "SELECT count(*) FROM `{$wpdb->prefix}options` WHERE `option_name` LIKE 'wpsfi_post_type' LIMIT 1" );
    if( !$wpsfi_post_type ){
        update_option( 'wpsfi_post_type',  wpsfi_post_type_list() );
    }
} );