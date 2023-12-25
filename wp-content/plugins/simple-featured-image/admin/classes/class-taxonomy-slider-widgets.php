<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Creating the widget 
class WPSFI_Taxonomy_Slider_Widget extends WP_Widget {
 
	function __construct() {
        parent::__construct(	
            'wpsfi_taxonomy_slider_widget',            
            __('WPSFI Slider', 'wp-simple-featured-image'), 
            array( 
                'description' => __( 'Displays a Taxonomy Slider', 'wp-simple-featured-image' ), 
            ) 
        );
        add_action( 'widgets_init', array( $this, 'wpb_load_widget' ) );
	}
	function wpb_load_widget() {
        register_widget( 'wpsfi_taxonomy_slider_widget' );
    }	
	public function widget( $args, $instance ) {
        $widgetID       = $args['widget_id'];
        $title          = apply_filters( 'widget_title', $instance['title'] );
        $wpsfi_taxonomy = apply_filters( 'widget_wpsfi_taxonomy', $instance['taxonomy'] );	
        $animation      = apply_filters( 'widget_wpsfi_slideshow', $instance['wpsfi_slideshow'] );	
        $animationLoop  = apply_filters( 'widget_wpsfi_loop', $instance['wpsfi_loop'] );	
        $mousewheel     = apply_filters( 'widget_wpsfi_mousewheel', $instance['wpsfi_mousewheel'] );	
        $controlNav     = apply_filters( 'widget_wpsfi_control_nav', $instance['wpsfi_control_nav'] );	
        $directionNav   = apply_filters( 'widget_wpsfi_direction_nav', $instance['wpsfi_direction_nav'] );
        $animationTitle = apply_filters( 'widget_wpsfi_title_animation', $instance['wpsfi_title_animation'] );
        $wpsfi_count    = apply_filters( 'widget_wpsfi_taxonomy', $instance['count'] );	


        $animation      = ( $animation ) ? 'true' : 'false' ;
        $animationLoop  = ( $animationLoop ) ? 'true' : 'false' ;
        $mousewheel     = ( $mousewheel ) ? 'true' : 'false' ;
        $controlNav     = ( $controlNav ) ? 'true' : 'false' ;
        $directionNav   = ( $directionNav ) ? 'true' : 'false' ;
        // Add custom class for the Flexslider
        $args['before_widget'] = str_replace('class="', "class=\"flexslider ", $args['before_widget'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];
        if( $wpsfi_taxonomy ){
            $terms = get_terms( array(
                'taxonomy'      => $wpsfi_taxonomy,
                'hide_empty'    => false,
            ) );
            if( !empty( $terms ) ){
                ob_start();
                ?>
                <ul class="slides"> 
                    <?php
                    foreach ($terms as $term ) {
                        $_termID    = $term->term_id;
                        $_termName  = $term->name;
                        $_termCount = $term->count;
                        $_termLink  = get_term_link( $_termID );
                        $thumbnail  = wpsfi_get_featured_image_url( $_termID);
                        $full_image = wpsfi_get_featured_image_url( $_termID, false, 'full' );
                        $counter_html = ( $wpsfi_count  ) ? ' ('.$_termCount.')' : '';
                        ?>
                        <li data-thumb="<?php echo $thumbnail; ?>" data-src="<?php echo $full_image; ?>">
                            <img src="<?php echo $full_image; ?>" />
                            <a href="<?php echo $_termLink; ?>"><p class="flex-caption"><?php echo $_termName; ?><?php echo $counter_html; ?></p></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <script>
                    jQuery(document).ready(function($){
                        $('#<?php echo $widgetID; ?>').flexslider({
                            animation: "<?php echo $animation; ?>",
                            animationLoop: <?php echo $animationLoop; ?>,
                            // itemWidth: 600,
                            itemMargin: 0,
                            minItems: 1,
                            maxItems: 6,      
                            mousewheel: <?php echo $mousewheel; ?>,  
                            // direction: "<?php echo $direction; ?>",
                            controlNav: <?php echo $controlNav; ?>,
                            directionNav: <?php echo $directionNav; ?>,
                            before: function(slider){
                            <?php if( $minItems == 1 ): ?>
                                $(slider).find(".flex-active-slide").find('.flex-caption').each(function(){
                                    $(this).removeClass("animated <?php echo $animationTitle; ?>");
                                });
                            <?php else: ?>
                                $(slider).find('.flex-caption').each(function(){
                                    $(this).removeClass("animated <?php echo $animationTitle; ?>");
                                });
                            <?php endif; ?>
                            },
                            after: function(slider){
                            <?php if( $minItems == 1 ): ?>
                                $(slider).find(".flex-active-slide").find('.flex-caption').addClass("animated <?php echo $animationTitle; ?>");
                            <?php else: ?>
                                $(slider).find('.flex-caption').addClass("animated <?php echo $animationTitle; ?>");
                            <?php endif; ?>
                            },
                        });
                    });
                </script>
                <?php
                echo ob_get_clean();
            }
        }
        
		echo $args['after_widget'];
	}
			
	// Widget Backend 
	public function form( $instance ) {
        $title                  = $instance[ 'title' ];
        $wpsfi_taxonomy         = $instance[ 'taxonomy' ];        
        $wpsfi_slideshow        = $instance[ 'wpsfi_slideshow' ];        
        $wpsfi_loop             = $instance[ 'wpsfi_loop' ];        
        $wpsfi_mousewheel       = $instance[ 'wpsfi_mousewheel' ];      
        $wpsfi_control_nav      = $instance[ 'wpsfi_control_nav' ];      
        $wpsfi_direction_nav    = $instance[ 'wpsfi_direction_nav' ];      
        $wpsfi_animation        = $instance[ 'wpsfi_animation' ];              
        $wpsfi_direction        = $instance[ 'wpsfi_direction' ];              
        $wpsfi_title_animation  = $instance[ 'wpsfi_title_animation' ];                           
        $wpsfi_count            = $instance[ 'count' ];        
        $registered_taxonomies  = wpsfi_get_registered_taxonomy();
		?>
		<p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-simple-featured-image' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpsfi_taxonomy' ); ?>"><?php _e( 'Select Taxonomy:', 'wp-simple-featured-image' ); ?></label>
            <?php if( !empty( $registered_taxonomies ) ): ?>
                    <select name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'wpsfi_taxonomy' ); ?>" >
                        <?php foreach( $registered_taxonomies as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php selected( $wpsfi_taxonomy , $key, true ); ?>><?php echo $value; ?></option>
                        <?php endforeach; ?>
                    </select>
            <?php endif; ?>
        </p>  
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_slideshow' ); ?>" name="<?php echo $this->get_field_name( 'wpsfi_slideshow' ); ?>" value="1" <?php checked( $wpsfi_slideshow, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_slideshow' ); ?>"><?php _e( 'Animate slider automatically', 'wp-simple-featured-image' ); ?></label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_loop' ); ?>" name="<?php echo $this->get_field_name( 'wpsfi_loop' ); ?>" value="1" <?php checked( $wpsfi_loop, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_loop' ); ?>"><?php _e( 'Animation loop', 'wp-simple-featured-image' ); ?></label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_mousewheel' ); ?>" name="<?php echo $this->get_field_name( 'wpsfi_mousewheel' ); ?>" value="1" <?php checked( $wpsfi_mousewheel, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_mousewheel' ); ?>"><?php _e( 'Allows slider navigating via mousewheel', 'wp-simple-featured-image' ); ?></label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_control_nav' ); ?>" name="<?php echo $this->get_field_name( 'wpsfi_control_nav' ); ?>" value="1" <?php checked( $wpsfi_control_nav, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_control_nav' ); ?>"><?php _e( 'Create navigation for paging control of each slide.', 'wp-simple-featured-image' ); ?></label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_direction_nav' ); ?>" name="<?php echo $this->get_field_name( 'wpsfi_direction_nav' ); ?>" value="1" <?php checked( $wpsfi_direction_nav, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_direction_nav' ); ?>"><?php _e( 'Create navigation for previous/next navigation', 'wp-simple-featured-image' ); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpsfi_animation' ); ?>"><?php _e( 'Select animation type:', 'wp-simple-featured-image' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'wpsfi_animation' ); ?>" id="<?php echo $this->get_field_id( 'wpsfi_animation' ); ?>" >
                <option value="slide" <?php selected( $wpsfi_animation , 'slide', true ); ?>><?php _e('Slide', 'wp-simple-featured-image' ); ?></option>
                <option value="fade" <?php selected( $wpsfi_animation , 'fade', true ); ?>><?php _e('Fade', 'wp-simple-featured-image' ); ?></option>
            </select>
        </p> 
        <p>
            <label for="<?php echo $this->get_field_id( 'wpsfi_direction' ); ?>"><?php _e( 'Select the sliding direction:', 'wp-simple-featured-image' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'wpsfi_direction' ); ?>" id="<?php echo $this->get_field_id( 'wpsfi_direction' ); ?>" >
                <option value="horizontal" <?php selected( $wpsfi_direction , 'horizontal', true ); ?>><?php _e('Horizontal', 'wp-simple-featured-image' ); ?></option>
                <option value="vertical" <?php selected( $wpsfi_direction , 'vertical', true ); ?>><?php _e('Vertical', 'wp-simple-featured-image' ); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpsfi_title_animation' ); ?>"><?php _e( 'Label Animation:', 'wp-simple-featured-image' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'wpsfi_title_animation' ); ?>" id="<?php echo $this->get_field_id( 'wpsfi_title_animation' ); ?>" >
                <option value="bounceInDown" <?php selected( $wpsfi_title_animation , 'bounceInDown', true ); ?>><?php _e('bounceInDown', 'wp-simple-featured-image' ); ?></option>
                <optgroup label="<?php _e('Bouncing Entrances', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('bouncing_entrances') as $bouncing_classes ): ?>
                    <option value="<?php echo $bouncing_classes; ?>" <?php selected( $wpsfi_title_animation , $bouncing_classes, true ); ?>><?php echo $bouncing_classes; ?></option>
                <?php endforeach; ?>
                </optgroup>  
                <optgroup label="<?php _e('Fading Entrances', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('fading_entrances') as $fading_classes ): ?>
                    <option value="<?php echo $fading_classes; ?>" <?php selected( $wpsfi_title_animation , $fading_classes, true ); ?>><?php echo $fading_classes; ?></option>
                <?php endforeach; ?>
                </optgroup> 
                <optgroup label="<?php _e('Rotating Entrances', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('rotating_entrances') as $rotating_classes ): ?>
                    <option value="<?php echo $rotating_classes; ?>" <?php selected( $wpsfi_title_animation , $rotating_classes, true ); ?>><?php echo $rotating_classes; ?></option>
                <?php endforeach; ?>
                </optgroup> 
                <optgroup label="<?php _e('Sliding Entrances', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('sliding_entrances') as $sliding_classes ): ?>
                    <option value="<?php echo $sliding_classes; ?>" <?php selected( $wpsfi_title_animation , $sliding_classes, true ); ?>><?php echo $sliding_classes; ?></option>
                <?php endforeach; ?>
                </optgroup>
                <optgroup label="<?php _e('Zoom Entrances', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('zoom_entances') as $zoom_classes ): ?>
                    <option value="<?php echo $zoom_classes; ?>" <?php selected( $wpsfi_title_animation , $zoom_classes, true ); ?>><?php echo $zoom_classes; ?></option>
                <?php endforeach; ?>
                </optgroup>
                <optgroup label="<?php _e('Specials', 'wp-simple-featured-image' ); ?>">
                <?php foreach( wpsfi_animate_classes('specials') as $specials_classes ): ?>
                    <option value="<?php echo $specials_classes; ?>" <?php selected( $wpsfi_title_animation , $specials_classes, true ); ?>><?php echo $specials_classes; ?></option>
                <?php endforeach; ?>
                </optgroup>
            </select>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="1" <?php checked( $wpsfi_count, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_count' ); ?>"><?php _e( 'Show Post Count', 'wp-simple-featured-image' ); ?></label>
        </p>
		<?php 
	}
		
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
        $instance['title']              = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['taxonomy']           = ( ! empty( $new_instance['taxonomy'] ) ) ? strip_tags( $new_instance['taxonomy'] ) : '';
        $instance['wpsfi_slideshow']    = ( ! empty( $new_instance['wpsfi_slideshow'] ) ) ? true : false;
        $instance['wpsfi_loop']         = ( ! empty( $new_instance['wpsfi_loop'] ) ) ? true : false;
        $instance['wpsfi_mousewheel']   = ( ! empty( $new_instance['wpsfi_mousewheel'] ) ) ? true : false;
        $instance['wpsfi_control_nav']  = ( ! empty( $new_instance['wpsfi_control_nav'] ) ) ? true : false;
        $instance['wpsfi_direction_nav'] = ( ! empty( $new_instance['wpsfi_direction_nav'] ) ) ? true : false;
        $instance['wpsfi_animation']    = ( ! empty( $new_instance['wpsfi_animation'] ) ) ? strip_tags( $new_instance['wpsfi_animation'] ) : 'slide';
        $instance['wpsfi_direction']    = ( ! empty( $new_instance['wpsfi_direction'] ) ) ? strip_tags( $new_instance['wpsfi_direction'] ) : 'horizontal';
        $instance['wpsfi_title_animation']    = ( ! empty( $new_instance['wpsfi_title_animation'] ) ) ? strip_tags( $new_instance['wpsfi_title_animation'] ) : 'bounceInDown';
        $instance['count']              = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : 0;
		return $instance;
	}
}