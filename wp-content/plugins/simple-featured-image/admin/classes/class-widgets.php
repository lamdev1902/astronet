<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Creating the widget 
class WPSFI_Taxonomy extends WP_Widget {
 
	function __construct() {
        parent::__construct(	
            'wpsfi_taxonomy',            
            __('WPSFI Taxonomies', 'wp-simple-featured-image'), 
            array( 
                'description' => __( 'Displays a list of taxonomies', 'wp-simple-featured-image' ), 
            ) 
        );
        add_action( 'widgets_init', array( $this, 'wpb_load_widget' ) );
	}
	function wpb_load_widget() {
        register_widget( 'wpsfi_taxonomy' );
    }	
	public function widget( $args, $instance ) {
        $title          = apply_filters( 'widget_title', $instance['title'] );
        $wpsfi_taxonomy = apply_filters( 'widget_wpsfi_taxonomy', $instance['taxonomy'] );	
        $wpsfi_display = apply_filters( 'widget_wpsfi_display', $instance['display'] );	
        $wpsfi_count    = apply_filters( 'widget_wpsfi_taxonomy', $instance['count'] );	
        $tax_counter = '';
		echo $args['before_widget'];
		if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];
        if( $wpsfi_taxonomy ){
            $terms = get_terms( array(
                'taxonomy'      => $wpsfi_taxonomy,
                'hide_empty'    => false,
            ) );
            if( !empty( $terms ) ){
                if( $wpsfi_display != 'dropdown' ){
                    echo '<ul class="wpfsi-taxonomy-list">';
                }else{
                    echo '<label class="screen-reader-text" for="cat">'.__( 'Taxonomy:', 'wp-simple-featured-image' ).'</label>';
                    echo '<select  onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">';
                }               
                foreach ($terms as $term ) {
                    // The $term is an object, so we don't need to specify the $taxonomy.
                    $term_link = get_term_link( $term );
                    
                    // If there was an error, continue to the next term.
                    if ( is_wp_error( $term_link ) ) {
                        continue;
                    }

                    if( $wpsfi_count ){
                        $tax_counter = ' ('.$term->count.')';
                    }

                    if( $wpsfi_display != 'dropdown' ){
                        echo '<li>';
                        if( $wpsfi_display == 'thumbnail' ){
                            echo '<a href="'.$term_link.'">'.wpsfi_display_image($term->term_id, 'thumbnail', 'wpsfi-taxonomy', '', '', false ).'</a>';
                            do_action( 'wpsfi_after_widget_thumbnnail', $term );
                        }else{
                            echo $term->name.$tax_counter;
                        }                     
                        echo '</li>';
                    }else{
                        echo '<option value="'.$term_link.'">'.$term->name.$tax_counter.'</option>';
                    }
                    
                }
                if( $wpsfi_display != 'dropdown' ){
                    echo '</ul>';
                }else{
                    echo '</select>';
                }           
            }
        }
        
		echo $args['after_widget'];
	}
			
	// Widget Backend 
	public function form( $instance ) {
        $title          = $instance[ 'title' ];
        $wpsfi_taxonomy = $instance[ 'taxonomy' ];        
        $wpsfi_display = $instance[ 'display' ];              
        $wpsfi_count    = $instance[ 'count' ];        
        $registered_taxonomies = wpsfi_get_registered_taxonomy();
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
            <label for="<?php echo $this->get_field_id( 'wpsfi_display' ); ?>"><?php _e( 'Select Display Type:', 'wp-simple-featured-image' ); ?></label>
            <select name="<?php echo $this->get_field_name( 'display' ); ?>" id="<?php echo $this->get_field_id( 'wpsfi_display' ); ?>" >
                <option value="dropdown" <?php selected( $wpsfi_display , 'dropdown', true ); ?>><?php _e('Dropdown', 'wp-simple-featured-image' ); ?></option>
                <option value="list" <?php selected( $wpsfi_display , 'list', true ); ?>><?php _e('List', 'wp-simple-featured-image' ); ?></option>
                <option value="thumbnail" <?php selected( $wpsfi_display , 'thumbnail', true ); ?>><?php _e('Thumbnail', 'wp-simple-featured-image' ); ?></option>
            </select>
        </p> 
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'wpsfi_count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="1" <?php checked( $wpsfi_count, 1, true ); ?>>
            <label for="<?php echo $this->get_field_id( 'wpsfi_count' ); ?>"><?php _e( 'Show Count', 'wp-simple-featured-image' ); ?></label><br/>
            <span class="description"><?php _e('Note: This will not apply on display thumbnail', 'wp-simple-featured-image'); ?></p>
        </p>
		<?php 
	}
		
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['taxonomy'] = ( ! empty( $new_instance['taxonomy'] ) ) ? strip_tags( $new_instance['taxonomy'] ) : '';
        $instance['display'] = ( ! empty( $new_instance['display'] ) ) ? strip_tags( $new_instance['display'] ) : 0;
        $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : 0;
		return $instance;
	}
}