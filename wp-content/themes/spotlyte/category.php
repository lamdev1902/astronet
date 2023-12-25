<?php 
$terms_current = get_queried_object();
$src = wpsfi_display_image( $terms_current->term_id );
get_header(); 
?>
<main id="content">
	<div class="interview-top ab-top position-relative">
		<div class="text-vertical">
		  <h3><?php echo $terms_current->name;  ?></h3>
		  <div class="vertical-box__dash"></div>
		  <span>All related content</span>
		</div>
		<div class="container">
			<div class="box">
				<div class="featured image-fit">
					<?php echo $src; ?>
				</div>
				<div class="info text-center">
					<h1><?php echo $terms_current->name;  ?> <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-star.svg" alt=""></h1>
					<p><?php echo $terms_current->description;  ?></p>
					<i class="far fa-long-arrow-down"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="interview-list">
		<div class="container">
			<div class="pd-list list-flex">
				<?php
					$args = array(
						'posts_per_page'	=> 12,
						'offset' => 0, 
						'tax_query'			=> array(
							array(
								'taxonomy'		=> 'category',
								'field'			=> 'id',
								'terms'			=> $terms_current->term_id
							)
						)
					);
					 $the_query = new WP_Query( $args );
					while ($the_query->have_posts() ) : $the_query->the_post();
					$countp = $the_query ->found_posts;
					$author_id = get_post_field ('post_author', $post->ID);
					$author_name = get_the_author_meta( 'nickname' , $author_id ); 
					$author_url = get_author_posts_url( $author_id );
				?>
				<div class="pd-it animated fade-in">
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

			</div>
			<?php if ($countp > 12): ?>
			<div class="text-center">
				<a class="btn-white" href="javascript:;" id="loadmore" data-page="2" data-terms="<?php echo $terms_current->term_id ?>">READ MORE</a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>
<script>

	$(document).ready(function(){
		offset = 12
		$('#loadmore').on('click',function(e){
			e.preventDefault();
			var $page = $(this).attr('data-page'),
				$term = $(this).attr('data-terms'),
				ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

			$.ajax({
				type: "GET",
	            url: ajaxurl,
	            data: 'page='+$page+'&term='+$term+'&action=load_more_post',
	            success: function (data) {
	            	if (data != 'end') {
						$('.pd-list').append(data); 
						offset = offset + 12; 
						if (offset >= <?php echo $countp; ?>) { 
							$('#loadmore').hide(); 
						}
					}

	            },
			});
		});
	});
</script>


