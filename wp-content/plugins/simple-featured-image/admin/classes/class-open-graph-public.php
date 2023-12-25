<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class WP_Simple_Featured_Open_Graph{
    public function __construct(){
        if( !get_option('wpsfi_enable_opengraph') ){
            return false;
        }
        add_action('wp_head', array( $this, 'meta_tags_fb'), 100 );
        // hook to add Open Graph Namespace
		add_filter( 'language_attributes', array( $this, 'add_namespace' ), 100 );
    }

    public function meta_tags_fb(){
        global $post;
        $wpsfi_taxonomy = get_option( 'wpsfi_taxonomy' );
        /* OG Image : Requirements
            Ref: https://ogp.me
            <meta property="og:title" content="The Rock" />
            <meta property="og:description" content="Sean Connery found fame and fortune as the suave, sophisticated British agent, James Bond." />
            <meta property="og:type" content="video.movie" />
            <meta property="og:url" content="https://www.imdb.com/title/tt0117500/" />
            <meta property="og:image" content="https://ia.media-imdb.com/images/rock.jpg" />
        */
        $diplay_meta_tags = false;
        $title          = '';
        $description    = '';
        $type           = 'website';
        $url            = '';
        $image          = '';
        // Single Page
        if( is_singular() ){
            $diplay_meta_tags = true;
            if( has_post_thumbnail( get_the_ID() ) ){
                $image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
            }else{
                $image = wpsfi_placeholder_image();
            }
            $title = get_the_title();
            $description = get_the_excerpt();
            $url = get_the_permalink();
        }

        // Category Page
        if ( is_category() ) {

            $diplay_meta_tags = true;
            $term       = get_queried_object();
            $imageID    = get_term_meta( $term->term_id, 'wpsfi_tax_image_id', true );
            $image      = wp_get_attachment_image_url( $imageID, 'full' );
            
            $title      = $term->name;
            $description = $term->description;
            $url        = get_term_link($term->term_id) ;
        }

        if( is_archive() ){

            $diplay_meta_tags = true;
            $term       = get_queried_object();
            $imageID    = get_term_meta( $term->term_id, 'wpsfi_tax_image_id', true );
            $image      = wp_get_attachment_image_url( $imageID, 'full' );
            
            $title      = $term->name;
            $description = $term->description;
            $url        = get_term_link($term->term_id) ;
        }

        if( $diplay_meta_tags ){
            $title          = apply_filters( 'wpsfi_open_graph_title', $title );
            $description    = apply_filters( 'wpsfi_open_graph_description', $description );
            $type           = apply_filters( 'wpsfi_open_graph_type', $type );
            $url            = apply_filters( 'wpsfi_open_graph_url', $url );
            $image          = apply_filters( 'wpsfi_open_graph_image', $image );
            ?>
            <meta property="og:title" content="<?php echo trim( wp_strip_all_tags( stripslashes( $title ) ) ); ?>" />
            <meta property="og:description" content="<?php echo trim( wp_strip_all_tags( stripslashes( $description ) ) ); ?>" />
            <meta property="og:type" content="<?php echo trim( wp_strip_all_tags( stripslashes( $type ) ) ); ?>" />
            <meta property="og:url" content="<?php echo esc_url($url); ?>" />
            <meta property="og:image" content="<?php echo esc_url( $image ); ?>" />
            <?php
        }
        
    }

    // Add namespace
    function add_namespace( $output ){
        if ( stristr($output, 'xmlns:og') ) {
            //Already there
        } else {
            //Let's add it
            $output=$output . ' xmlns:og="http://ogp.me/ns#"';
        }
        if ( stristr($output, 'xmlns:fb') ) {
            //Already there
        } else {
            //Let's add it
            $output=$output . ' xmlns:fb="http://ogp.me/ns/fb#"';
        }

        return $output;
    }
}