<?php 
$postid = get_the_ID();
$author_id = get_post_field ('post_author', $post_id);
$display_name = get_the_author_meta( 'nickname' , $author_id ); 
$author_url = get_author_posts_url( $author_id );
get_header(); 
the_post();
$post_terms = wp_get_post_terms($postid,'category');
$post_terms_name = $post_terms[0]->name;
$post_terms_id = $post_terms[0]->term_id;
?>
<main id="content">
	<div class="single-top-2">
		<div class="container">
			<div class="info text-center">
				<h4 class="type-style-overline font-15"><?php echo $post_terms_name; ?></h4>
				<h1><?php the_title(); ?></h1>
				<h5 class="text-uppercase font-effra author-date">
					<a href="<?php echo $author_url; ?>"><?php echo $display_name; ?></a>
					<span class="pipe">|</span>
					<?php echo get_the_date('F d, y'); ?>
				</h5>
				<div class="social">
					<a target="_blank" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;" href="https://www.facebook.com/sharer.php?u=<?php the_permalink() ?>&image=<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)) ?>"><i class="fab fa-facebook-f"></i></a>
					<a target="_blank" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;" class="rst-icon-pinterest" href="https://pinterest.com/pin/create/button/?url=<?php the_permalink() ?>&media=<?php echo wp_get_attachment_url(get_post_thumbnail_id($post->ID)) ?>"><i class="fab fa-pinterest-p"></i></a>
					<a target="_blank" onclick="window.open(this.href, 'mywin','left=50,top=50,width=600,height=350,toolbar=0'); return false;" href="https://twitter.com/share?text=<?php echo str_replace(" ", "+", strip_tags(get_the_title()) ) ?>&amp;url=<?php the_permalink() ?>"><i class="fab fa-twitter" aria-hidden="true"></i></a>
				</div>
			</div>
			<div class="featured">
				<?php if(get_the_post_thumbnail($postid,'full')) {
				the_post_thumbnail('full');
				echo '<figcaption class="text-uppercase font-effra">'.get_the_post_thumbnail_caption($postid).'</figcaption>';
			} else { ?>
							<img src="<?php echo get_field('fimg_default','option'); ?>" alt="Image default">
							<?php } ?>
			</div>
		</div>
	</div>
	<div class="single-center">
		<div class="container">
			<div class="single-custom">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
	<div class="single-related text-center">
		<div class="container">
			<h4 class="type-style-overline font-15 text-center">Related</h4>
			<div class="related-list list-flex flex-middle">
				<?php 
					$term_name1 = $post_terms[1]->name;
					$term_id1 = $post_terms[1]->term_id;
					$term_name2 = $post_terms[2]->name;
					$term_id2 = $post_terms[2]->term_id;
					$src1 = wpsfi_display_image( $term_id1 );
					$src2 = wpsfi_display_image( $term_id2 );
				?>
				<?php if($term_id1){ ?>
				<div class="related-it">
					<a href="<?php echo get_term_link($term_id1); ?>">
						<div class="featured image-fit">
							<img src="<?php echo $src1; ?>" alt="">
						</div>
						<h3><?php echo $term_name1; ?></h3>
					</a>
				</div>
				<?php } ?>
				<?php if($term_id2){ ?>
				<div class="dots"><svg version="1.1" width="3px" height="3px" viewBox="0 0 3 3" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd" stroke-width="1"><g transform="translate(-458 -97)" fill="#fff"><g transform="translate(158 72)"><circle cx="301.5" cy="26.5" r="1.5"></circle></g></g></g></svg></div>
				<div class="related-it">
					<a href="<?php echo get_term_link($term_id2); ?>">
						<div class="featured image-fit">
							<img src="<?php echo $src2; ?>" alt="">
						</div>
						<h3><?php echo $term_name2; ?></h3>
					</a>
				</div>
				<?php } ?>
			</div>
			<div class="divider divider__light"></div>
			<div class="tags-list">
				<?php
					$i = 1;
					$posttags = get_the_tags();
					if ($posttags) {
					  foreach($posttags as $tag) {
					  $tag_link = get_tag_link( $tag->term_id );
				?>
				<?php if($i != 1) { ?> <span>,</span> <?php } ?>
				<a href="<?php echo  $tag_link; ?>"><?php echo $tag->name; ?></a>
				<?php $i++; } } ?>
			</div>
		</div>
	</div>
	<div class="single-pd-other">
		<div class="container">
			<h4 class="other-title type-style-overline font-15 text-center">YOU MIGHT ALSO LIKE</h4>
			<div class="pd-list list-flex">
				<?php
					$args = array(
						'posts_per_page'	=> 2	
					);
					 $the_query = new WP_Query( $args );
					while ($the_query->have_posts() ) : $the_query->the_post();
					$post_author_id = get_post_field ('post_author', $post->ID);
					$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
					$post_author_url = get_author_posts_url( $post_author_id );
				?>
				<div class="pd-it animated fade-in">
					<div class="featured image-fit">
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
					</div>
					<div class="info">
						<?php $cat = get_the_category( $post->ID);
						if(!empty($cat) && count($cat) > 0) :  ?>
						<h4 class="type-style-overline font-15"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
						<?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<h5 class="text-uppercase font-effra author-date">
							<a href="<?php echo $post_author_url; ?>">BY <?php echo $post_display_name; ?></a>
							<?php echo get_the_date('F d, y'); ?>
						</h5>
					</div>
				</div>
				<?php
					endwhile;
					wp_reset_query();
				?>
			</div>
		</div>
	</div>
	<div class="single-recommended">
		<div class="container">
			<h4 class="recommended-title type-style-overline font-15 text-center">RECOMMENDED READING</h4>
			<span class="divider divider__dark on-sp"></span>
			<div class="pd-list list-flex">
				<?php
					$args = array(
						'posts_per_page'	=> 4,
						'offset'           => 1,	
						'tax_query'			=> array(
							array(
								'taxonomy'		=> 'category',
								'field'			=> 'id',
								'terms'			=> $post_terms_id
							)
						)

					);
					 $the_query = new WP_Query( $args );
					while ($the_query->have_posts() ) : $the_query->the_post();
					$post_author_id = get_post_field ('post_author', $post->ID);
					$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
					$post_author_url = get_author_posts_url( $post_author_id );
				?>
				<div class="pd-it animated fade-in">
					<div class="featured image-fit">
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
					</div>
					<div class="info">
						<?php $cat = get_the_category( $post->ID);
						if(!empty($cat) && count($cat) > 0) :  ?>
						<h4 class="type-style-overline font-15"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
						<?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<h5 class="text-uppercase font-effra author-date">
							<a href="<?php echo $post_author_url; ?>">BY <?php echo $post_display_name; ?></a>
							<?php echo get_the_date('F d, y'); ?>
						</h5>
					</div>
				</div>
				<?php
					endwhile;
					wp_reset_query();
				?>
			</div>
		</div>
	</div>
</main>
<?php get_footer(); ?>
<script>
	jQuery(function($){
		$('.wp-caption-small').parent().addClass('wp-caption-small');
	});
</script>