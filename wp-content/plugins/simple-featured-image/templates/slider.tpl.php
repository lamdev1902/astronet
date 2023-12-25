<div id="wpsfi-shortcode-slider" class="flexslider">
    <ul class="slides"> 
        <?php
        foreach ($terms as $term ) {
            $_termID    = $term->term_id;
            $_termName  = $term->name;
            $_termCount = $term->count;
            $_termLink  = get_term_link( $_termID );
            $thumbnail  = wpsfi_get_featured_image_url( $_termID);
            $full_image = wpsfi_get_featured_image_url( $_termID, false, 'full' );
            ?>
            <li data-thumb="<?php echo $thumbnail; ?>" data-src="<?php echo $full_image; ?>">
                <img src="<?php echo $full_image; ?>" />
                <a href="<?php echo $_termLink; ?>"><p class="flex-caption"><?php echo $_termName; ?> (<?php echo $_termCount; ?>)</p></a>
            </li>
            <?php
        }
        ?>
    </ul>
</div>
<script>
    jQuery(document).ready(function($){
        $('#wpsfi-shortcode-slider').flexslider({
            slideshow: "<?php echo $slideshow; ?>",
            slideshowSpeed: <?php echo $slideshowSpeed; ?>,
            animationSpeed: <?php echo $animationSpeed; ?>,
            animation: "<?php echo $animation; ?>",
            animationLoop: <?php echo $animationLoop; ?>,
            itemWidth: <?php echo $itemWidth; ?>,
            itemMargin: <?php echo $itemMargin; ?>,
            minItems: <?php echo $minItems; ?>,
            maxItems: <?php echo $maxItems; ?>,      
            mousewheel: <?php echo $mousewheel; ?>,  
            direction: "<?php echo $direction; ?>",
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