<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
class WP_Simple_Featured_Image{
    public $wpsfi_taxonomy = array();
    public function __construct(){
        $wpsfi_taxonomy         = get_option( 'wpsfi_taxonomy' );
        $wpsfi_post_type        = get_option( 'wpsfi_post_type' );
        $this->wpsfi_taxonomy   = $wpsfi_taxonomy;
        if( !empty( $wpsfi_taxonomy ) ){
            foreach( $wpsfi_taxonomy as $taxonomy ){
                add_action( $taxonomy.'_add_form_fields', array( $this, 'featured_taxonomy_field' ) ); 
                add_action( $taxonomy.'_edit_form_fields', array( $this, 'edit_featured_taxonomy_field' ) ); 

                // manage_edit-product_cat_columns
                add_filter('manage_edit-'.$taxonomy.'_columns', array( $this, 'taxonomy_columns_header' ) );
                add_filter('manage_'.$taxonomy.'_custom_column', array( $this, 'taxonomy_columns_content_taxonomy' ), 10, 3 );
            }
        }     
        if( !empty( $wpsfi_post_type ) ){
            foreach ( $wpsfi_post_type as $post_type ) {
                add_filter('manage_edit-'.$post_type.'_columns', array( $this, 'post_columns_header' ) );
                add_filter('manage_'.$post_type.'_posts_custom_column', array( $this, 'post_columns_content' ), 10, 2 );
            } 
        }

        add_filter('manage_edit-post_columns', array( $this, 'post_columns_header' ) );
        add_filter('manage_posts_custom_column', array( $this, 'post_columns_content' ), 10, 2 );

        add_action( 'created_term', array( $this, 'save_wpsfi_taxonomy_fields' ), 10, 3 );
        add_action( 'edit_term', array( $this, 'save_wpsfi_taxonomy_fields' ), 10, 3 );
        // Register plugin menu
        add_action('admin_menu', array( $this, 'settings_menu' ) );

        // Register Admin Scripts
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        // Register Setting Option
        add_action( 'admin_init', array( $this, 'register_setting_options' ) );

        // Load PLugin Text Domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Shortcodes
        add_shortcode( 'wpsfi_image', array( $this, 'featured_image_shortcode' ) );
        add_shortcode( 'wpsfi_slider', array( $this, 'featured_image_slider_shortcode' ) );

        // Plugin Row Meta
        add_filter( 'plugin_row_meta', array( $this, 'plugin_action_links_callback' ), 10, 2 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'wp-simple-featured-image', false, WPSFI_BASENAME.'/languages' );
    }
    // Enqueue media to the Select image Script
    public function admin_scripts(){
        wp_enqueue_media();
    }

    public function register_setting_options(){
        register_setting( 'wpsfi_settings_group', 'wpsfi_taxonomy' );
        register_setting( 'wpsfi_settings_group', 'wpsfi_post_type' );
        register_setting( 'wpsfi_settings_group', 'wpsfi_enable_opengraph' );
    }

    public function plugin_action_links_callback( $plugin_meta, $plugin_file ){
        if( $plugin_file == WPSFI_FILE ){
            $plugin_meta[] = '<a href="'.admin_url('options-general.php?page=wpsfi-settings').'">' . __( 'Settings', 'wp-simple-featured-image' ) . '</a>';
			$plugin_meta[] = '<a href="https://www.paypal.me/jdegayojr" target="_blank"><img src="'.WPSFI_URL.'/admin/assets/images/coffee-cup.png" alt="'.__('Donate','wp-simple-featured-image' ).'"/> ' . __( 'Buy me a Coffee', 'wp-simple-featured-image' ) . '</a>';
		}
		return $plugin_meta;
    }

    public function settings_menu(){
        add_submenu_page(
            'options-general.php',
            __( 'Featured Image', 'wp-simple-featured-image' ),
            __( 'Featured Image', 'wp-simple-featured-image' ),
            'manage_options',
            'wpsfi-settings',
            array( $this, 'settings_menu_callback' )
        );
    }

    function featured_image_shortcode( $atts ){
        $atts = shortcode_atts( array(
            'term_id'   => '',
            'size'      => 'medium',
            'class'     => '',
            'width'     => '',
            'height'    => ''
        ), $atts );
        $imageID = get_term_meta( $atts['term_id'], 'wpsfi_tax_image_id', true );
        ob_start();
        if( $atts['width'] && $atts['height'] ){
            echo wp_get_attachment_image( $imageID, array( $atts['width'], $atts['height'] ), false, array( "class" => 'wpsfi-featured-image '.$atts['class'] ) );
        }else{
            echo wp_get_attachment_image( $imageID, $atts['size'], false, array( "class" => 'wpsfi-featured-image '.$atts['class'] ) );
        }       
        return ob_get_clean();
    }

    function save_wpsfi_taxonomy_fields( $term_id, $tt_id = '', $taxonomy = '' ){
        $wpsfi_taxonomy = $this->wpsfi_taxonomy;
        if( empty( $wpsfi_taxonomy ) ){
            $wpsfi_taxonomy = array();
        }
        if ( isset( $_POST['wpsfi_tax_image_id'] ) && in_array( $taxonomy, $wpsfi_taxonomy )  ) {
            update_term_meta( $term_id, 'wpsfi_tax_image_id', $_POST['wpsfi_tax_image_id'] );
        }
    }
    
    public function settings_menu_callback(){
        ?>
        <h1><?php _e('Simple Featured Image Setting', 'wp-simple-featured-image' ); ?></h1>
        <p class="description"><?php _e('Select taxonomies you want to enable Featured Image.', 'wp-simple-featured-image' ); ?></p>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'wpsfi_settings_group' );
            do_settings_sections( 'wpsfi_settings_group' );
            $wpsfi_taxonomy     = get_option( 'wpsfi_taxonomy' ) ? get_option( 'wpsfi_taxonomy' ) : array() ;
            $wpsfi_post_type    = get_option( 'wpsfi_post_type' ) ? get_option( 'wpsfi_post_type' ) : array() ;
            $registered_taxonomies  = wpsfi_get_registered_taxonomy();
            $post_type_list         = wpsfi_post_type_list();
            ?>
            <table class="form-table">
                <tr>
                    <th><?php _e('Select Taxonomy to Display Featured Image', 'wp-simple-featured-image' ); ?></th>
                    <td>
                        <?php if( !empty( $registered_taxonomies ) ): foreach( $registered_taxonomies as $key => $value): ?>
                        <input type="checkbox" name="wpsfi_taxonomy[]" value="<?php echo $key; ?>" <?php echo ( in_array( $key, $wpsfi_taxonomy ) ) ? 'checked' : '' ; ?>> <?php echo $value; ?><br/>
                        <?php endforeach; endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Select Post Type to Display Featured Image', 'wp-simple-featured-image' ); ?></th>
                    <td>
                        <?php if( !empty( $post_type_list ) ): foreach( $post_type_list as $post_slug => $postType): ?>
                        <input type="checkbox" name="wpsfi_post_type[]" value="<?php echo $post_slug; ?>" <?php echo ( in_array( $post_slug, $wpsfi_post_type ) ) ? 'checked' : '' ; ?>> <?php echo $postType; ?><br/>
                        <?php endforeach; endif; ?>
                    </td>
                </tr>

                <!-- OG Image settings -->
                <tr>
                    <th><?php _e('Enable Open Graph meta?', 'wp-simple-featured-image' ); ?></th>
                    <td>
                        <input type="checkbox" name="wpsfi_enable_opengraph" value="1" <?php checked( get_option('wpsfi_enable_opengraph'), 1); ?>><br/>
                        <p><?php esc_html_e('Note: This will add meta tags for Open Graph.', 'wp-simple-featured-image' ); ?></p>
                    </td>
                </tr>

            </table>
            <?php submit_button( __( 'Save Settings', 'wp-simple-featured-image' ) ); ?>
        </form>
        <?php
    }
    function taxonomy_columns_header( $defaults ){
        $defaults['wpsfi_featured_image']  = __('Featured Image', 'wp-simple-featured-image' );
        return $defaults;
    }
    function taxonomy_columns_content_taxonomy( $columns, $column, $id ){
        if ( 'wpsfi_featured_image' === $column ) {
            $imageID = get_term_meta( $id, 'wpsfi_tax_image_id', true );
            $image_url = wpsfi_get_featured_image_url( $id );
            $columns .= '<img data-id="'.$imageID.'" src="' . esc_url( $image_url ) . '" alt="' . esc_attr__( 'Thumbnail', 'wp-simple-featured-image' ) . '" class="wpsfi-image" height="48" width="48" />';
        }
        return $columns;
    }
    function post_columns_header( $defaults ){
        $defaults['wpsfi_featured_image']  = __('Featured Image', 'wp-simple-featured-image' );
        return $defaults;
    }
    function post_columns_content( $column, $post_id ){
        if ( 'wpsfi_featured_image' === $column ) {
            if( has_post_thumbnail( $post_id ) ){
                echo get_the_post_thumbnail( $post_id, array( 48, 48) );
            }else{
                echo '<img src="'.wpsfi_placeholder_image(false).'" alt="Thumbnail" class="wpsfi-image" height="48" width="48">';
            }
        }
    }
    public function featured_taxonomy_field(){
        ?>
        <div class="form-field wpsfi_taxonomy_featured_image">
            <label><?php _e( 'Featured Image', 'wp-simple-featured-image' ); ?></label>
            <div id="wpsfi_tax_image" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wpsfi_placeholder_image() ); ?>" width="60px" height="60px" /></div>
            <div style="line-height: 60px;">
                <input type="hidden" id="wpsfi_tax_image_id" name="wpsfi_tax_image_id" />
                <button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'wp-simple-featured-image' ); ?></button>
                <button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'wp-simple-featured-image' ); ?></button>
            </div>
            <script type="text/javascript">
                jQuery(document).ready( function($){
                    if ( ! $( '#product_cat_thumbnail_id' ).val() ) {
                        $( '.remove_image_button' ).hide();
                    }
                    var file_frame;
                    $( 'body' ).on( 'click', '.upload_image_button', function( event ) {
                        event.preventDefault();
                        if ( file_frame ) {
                            file_frame.open();
                            return;
                        }
                        file_frame = wp.media.frames.downloadable_file = wp.media({
                            title: '<?php _e( 'Choose a Fetured Image', 'wp-simple-featured-image' ); ?>',
                            button: {
                                text: '<?php _e( 'Use as Fetured Image', 'wp-simple-featured-image' ); ?>'
                            },
                            multiple: false
                        });
                        file_frame.on( 'select', function() {
                            var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;
                            $( '#wpsfi_tax_image_id' ).val( attachment.id );
                            $( '#wpsfi_tax_image' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                            $( '.remove_image_button' ).show();
                            $( '.upload_image_button' ).hide();
                        });
                        file_frame.open();
                    });

                    $( 'body' ).on( 'click', '.remove_image_button', function() {
                        $( '#wpsfi_tax_image' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wpsfi_placeholder_image() ); ?>' );
                        $( '#wpsfi_tax_image_id' ).val( '' );
                        $( '.remove_image_button' ).hide();
                        $( '.upload_image_button' ).show();
                        return false;
                    });

                    $( 'body' ).ajaxComplete( function( event, request, options ) {
                        if ( request && 4 === request.readyState && 200 === request.status
                            && options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

                            var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
                            if ( ! res || res.errors ) {
                                return;
                            }
                            $( '#wpsfi_tax_image' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wpsfi_placeholder_image() ); ?>' );
                            $( '#wpsfi_tax_image_id' ).val( '' );
                            return;
                        }
                    } );
                });
            </script>
            <div style="clear:both;"></div>
        </div>
        <?php
    }
    public function edit_featured_taxonomy_field( $term ){
        $thumbnail_id = get_term_meta( $term->term_id, 'wpsfi_tax_image_id', true );

        if ( $thumbnail_id ) {
            $image = wp_get_attachment_thumb_url( $thumbnail_id );
        } else {
            $image = wpsfi_placeholder_image();
        }
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label><?php _e( 'Featured Image', 'wp-simple-featured-image' ); ?></label></th>
            <td>
                <div id="wpsfi_tax_image" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
                <div style="line-height: 60px;">
                    <input type="hidden" id="wpsfi_tax_image_id" name="wpsfi_tax_image_id" value="<?php echo $thumbnail_id; ?>" />
                    <button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'wp-simple-featured-image' ); ?></button>
                    <button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'wp-simple-featured-image' ); ?></button>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready( function($){
                        var tax_thumbnail = $( '#wpsfi_tax_image' ).val();
                        if ( '0' === tax_thumbnail || '' === tax_thumbnail ) {
                            $( '.remove_image_button' ).hide();
                        }
                        var file_frame;
                        $( 'body' ).on( 'click', '.upload_image_button', function( event ) {
                            event.preventDefault();
                            if ( file_frame ) {
                                file_frame.open();
                                return;
                            }
                            file_frame = wp.media.frames.downloadable_file = wp.media({
                                title: '<?php _e( 'Choose a Fetured Image', 'wp-simple-featured-image' ); ?>',
                                button: {
                                    text: '<?php _e( 'Use as Fetured Image', 'wp-simple-featured-image' ); ?>'
                                },
                                multiple: false
                            });
                            file_frame.on( 'select', function() {
                                var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                                var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;
                                $( '#wpsfi_tax_image_id' ).val( attachment.id );
                                $( '#wpsfi_tax_image' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                                $( '.remove_image_button' ).show();
                                $( '.upload_image_button' ).hide();
                            });
                            file_frame.open();
                        });

                        $( 'body' ).on( 'click', '.remove_image_button', function() {
                            $( '#wpsfi_tax_image' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wpsfi_placeholder_image() ); ?>' );
                            $( '#wpsfi_tax_image_id' ).val( '' );
                            $( '.remove_image_button' ).hide();
                            $( '.upload_image_button' ).show();
                            return false;
                        });
                    });
                </script>
                <div style="clear:both;"></div>
            </td>
        </tr>
        <?php
    }
    function featured_image_slider_shortcode( $atts ){
        $atts = shortcode_atts( array(
            'taxonomy'       => 'category',
            'hide_empty'     => 'false',
            'slideshow'      => 'true',
            'slideshow_speed' => 7000,
            'animation'      => "slide",
            'animation_speed' => 600,
            'animation_loop' => 'true',
            'animation_title' => 'bounceInDown',
            'item_width'     => 600,
            'item_margin'    => 2,
            'min_items'      => 1,
            'max_items'      => 6,  
            'direction'     => 'horizontal',
            'mousewheel'    => 'false',
            'control_nav'    => 'true',
            'direction_nav'  => 'true'
        ), $atts );

        $taxonomy       = $atts['taxonomy'];
        $hide_empty     = $atts['hide_empty'];
        $slideshow      = $atts['slideshow'];
        $animation      = $atts['animation'];
        $slideshowSpeed = $atts['slideshow_speed'];
        $animationSpeed = $atts['animation_speed'];
        $animationLoop  = $atts['animation_loop'];
        $animationTitle = $atts['animation_title'];
        $itemWidth      = $atts['item_width'];
        $itemMargin     = $atts['item_margin'];
        $minItems       = $atts['min_items'];
        $maxItems       = $atts['max_items'];
        $direction      = $atts['direction'];
        $mousewheel     = $atts['mousewheel'];
        $controlNav     = $atts['control_nav'];
        $directionNav   = $atts['direction_nav'];

        // Reset Min Items to 1 when the direction is Vertical
        if( $direction != 'horizontal' ){
            $minItems = 1; 
        }
        // Reset the Item Width and Item Margin when the minumum Item is equal to 1
        if( $minItems == 1 ){
            $itemWidth = 0;
            $itemMargin = 0;
        }

        ob_start();
        $terms = get_terms( array(
            'taxonomy'      => $taxonomy,
            'hide_empty'    => $hide_empty,
        ) );
        if( !empty( $terms ) ){
            require_once( WPSFI_PATH. 'templates/slider.tpl.php');
        }        
        $output = ob_get_clean();
        return $output;
    }
}