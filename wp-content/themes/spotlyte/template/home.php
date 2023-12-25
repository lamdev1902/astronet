<?php 
/* Template Name: Home */
$pageid = get_the_ID();
get_header(); 
the_post();
$post_id= get_the_ID();
?>
<main id="content">
	<div class="home-top">
		<div class="container">
			<section class="snack-bar">
				<div class="snack-bar-links">
					<a href="#" class="snack-bar-link">
						<div class="snack-bar-link__text">
							<div class="snack-bar-link__number">
								96
							</div>
							<div class="snack-bar-link__category">
								Years of Fact-Based Parenting Advice
							</div>
						</div>
					</a>
					<a href="#" class="snack-bar-link">
						<div class="snack-bar-link__text">
							<div class="snack-bar-link__number">
								8000+
							</div>
							<div class="snack-bar-link__category">
								Expertly Written And Reviewed Articles
							</div>
						</div>
					</a>
					<a href="#" class="snack-bar-link">
						<div class="snack-bar-link__text">
							<div class="snack-bar-link__number">
								40MM
							</div>
							<div class="snack-bar-link__category">
								Families Supported Annually
							</div>
						</div>
					</a>
				</div>
			</section>
			<div class="list-flex htop-box">
				<div class="left">
					<div class="big">
						<?php 
							$post_top = get_field('post_top', $pageid) ;
							$post_top_id = $post_top->ID;
							$author_top_id = get_post_field ('post_author', $post_id);
							if($post_top){
						?>
						<div class="featured image-fit">
							<a href="<?php echo get_permalink($post_top_id); ?>"><img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post_top_id)); ?>" alt=""></a>
						</div>
						<div class="info info-pd">
							<?php $cat = get_the_category($post_top_id); ?>
							 <?php if(!empty($cat) && count($cat) > 0) :  ?>
								<h4 class="text-uppercase sub-title font-effra">
									<a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>">
										<?php echo $cat[0]->name; ?>
									</a>
								</h4>	
							<?php endif; ?>
							<h2><a href="<?php echo get_permalink($post_top_id); ?>"><?php echo $post_top->post_title; ?></a></h2>
							<p><?php echo get_the_excerpt($post_top_id); ?></p>
							<h5 class="text-uppercase font-effra author-date"><a href="<?php echo get_author_posts_url( $author_top_id ); ?>">by <?php echo get_the_author_meta( 'nickname' , $author_top_id ); ?></a><span class="pipe">|</span><?php echo get_the_date('F d, y',$post_top_id); ?></h5>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="right">
					<div class="title-section">
						<h3><span class="font-effra color-red">Latest News</span></h3>
					</div>
					<div class="latest-list">
						<?php
							$args = array(
								'posts_per_page'	=> 4	
							);
							 $the_query = new WP_Query( $args );
							while ($the_query->have_posts() ) : $the_query->the_post();
							$post_author_id = get_post_field ('post_author', $post->ID);
							$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
							$post_author_url = get_author_posts_url( $post_author_id );
						?>
						<div class="latest-it list-flex">
							<div class="featured image-fit">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
							</div>
							<div class="info animated fade-right">
								<?php $cat = get_the_category($post->ID);
									if(!empty($cat) && count($cat) > 0) :  ?>
									<h4 class="text-uppercase sub-title font-effra"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
								<?php endif; ?>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $post_author_url; ?>">by <?php echo $post_display_name; ?></a></h5>
							</div>
							<!-- <div class="line-border"></div> -->
						</div>
						<?php
							endwhile;
							wp_reset_query();
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="home-banner post">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 3, 
					'posts_per_page' => 1,
				);
				$the_query = new WP_Query( $args );
				$banner = $the_query->posts[0];
			?>
			<div class="hbanner-box">
				<div class="featured image-fit">
					<img src="<?php echo get_field('banner_image', $pageid) ?>" alt="">
				</div>
				<div class="info animated opacity-in">
				<?php 
					
					if($banner){
						$post_id = $banner->ID;
						$author_id = get_post_field('post_author', $post_id);
						$cat = get_the_category($banner->ID);
					?>
					<div class="info-banner animated opacity-in">
						<h4 class="text-uppercase sub-title font-effra"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
						<h4>
							<a href="<?php echo get_permalink($post_id); ?>">
							<span class="info-title">
								<?php echo $banner->post_title; ?>
							</span>
							<p class="info-content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam ipsam magni harum ut distinctio ducimus.</p>
								<a href="<?php echo get_author_posts_url( $author_id ); ?>" class="color-red">By <?php echo get_the_author_meta( 'nickname' , $author_id ); ?></a>
							</a>
						</h4>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<!-- <div class="home-fresh">
		<div class="container">
			<div class="list-flex hfresh-box">
				<div class="left">
					<div class="title-section animated opacity-in">
						<h3><?php echo get_field('fresh_title', $pageid); ?></h3>
					</div>
					<div class="hfresh-list">
						<?php 
							$fresh_post = get_field('fresh_select_post', $pageid);
							if($fresh_post){
								$i = 0;
								foreach($fresh_post  as $fresh){
									$fresh_item = $fresh['fresh_post_item'];
									if($fresh_item){
										$i++;
										$post_id = $fresh_item->ID;
										$author_id = get_post_field('post_author', $post_id);
									}
						?>
							<?php if($fresh_item){?>
							<div class="hfresh-it animated opacity-in">
								<h4>
									<a href="<?php echo get_permalink($post_id); ?>">
										<span class="number"><?php echo $i ?>.</span>
										<?php echo $fresh_item->post_title; ?>
										<a href="<?php echo get_author_posts_url( $author_id ); ?>" class="color-red">By <?php echo get_the_author_meta( 'nickname' , $author_id ); ?></a>
									</a>
								</h4>
							</div>
						<?php }
					}} ?>
					</div>
				</div>
				<div class="right text-center">
					<div class="follow-box animated opacity-in">
						<h3 class="font-effra text-center type-style-overline"><?php echo get_field('fresh_social_title',$pageid); ?></h3>
						<p><?php echo get_field('fresh_social_decription',$pageid); ?></p>
						<div class="social">
							<?php 
								$list_social = get_field('social', 'option');
								if($list_social){
									foreach($list_social as $social){
							?>
							<a href="<?php echo $social['link']; ?>" target="_blank"><i class="<?php echo $social['icon']; ?>"></i></a>
							<?php }} ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> -->
	<!-- <div class="home-medical">
		<div class="container">
			<div class="medical-title text-center">
				<div class="medical-icon">
					<img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-star.svg" alt="">
				</div>
				<h3 class="type-style-overline"><?php echo get_field('aesthetics_title',$pageid); ?></h3>
				<p><i><?php echo get_field('aesthetics_description',$pageid); ?></i></p>
			</div>
			<div class="on-pc medical-slider medical-list text-center sl-slider">
				<?php $aesthetics_post = get_field('aesthetics_list_post', $pageid); 
					if($aesthetics_post){
						foreach ($aesthetics_post as $aesthetics) {
						$aesthetics_item = $aesthetics['select_post'];
						$aesthetics_id = $aesthetics_item->ID;
				?>

				<div class="medical-it animated fade-in">
					<a href="<?php echo get_permalink($aesthetics_id); ?>">
						<div class="featured">
							<img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($aesthetics_id)); ?>" alt="">
						</div>
						<div class="info">
							<h4><?php echo $aesthetics_item->post_title; ?></h4>	
							<?php $treats = get_field('treats', $aesthetics_id); 
								if($treats){ ?>
								<div class="divider divider__dark"></div>
								<p><span class="color-yellow">Treats: </span><?php echo $treats; ?></p>
							<?php } ?>
						</div>
					</a>
				</div>
				<?php }} ?>

			</div>
			<div class="on-sp medical-list text-center">
				<?php $aesthetics_post = get_field('aesthetics_list_post', $pageid); 
					if($aesthetics_post){
						foreach ($aesthetics_post as $aesthetics) {
						$aesthetics_item = $aesthetics['select_post'];
						$aesthetics_id = $aesthetics_item->ID;
				?>
				<div class="medical-it animated fade-in">
					<a href="<?php echo get_permalink($aesthetics_id); ?>">
						<div class="featured">
							<img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($aesthetics_id)); ?>" alt="">
						</div>
						<div class="info">
							<h4><?php echo $aesthetics_item->post_title; ?></h4>	
							<?php $treats = get_field('treats', $aesthetics_id); 
								if($treats){ ?>
								<div class="divider divider__dark"></div>
								<p><span class="color-yellow">Treats: </span><?php echo $treats; ?></p>
							<?php } ?>
						</div>
					</a>
				</div>
				<?php }} ?>
			</div>
			<div class="text-center on-sp">
				<a class="btn-white" href="<?php echo get_term_link(31); ?>">READ MORE</a>
			</div>
		</div>
	</div>
	<div class="home-banner">
		<div class="container">
			<div class="hbanner-box">
				<div class="featured image-fit">
					<img src="<?php echo get_field('who_background',$pageid); ?>" alt="">
				</div>
				<div class="info animated opacity-in">
					<h3><?php echo get_field('who_title',$pageid); ?></h3>
					<p><?php echo get_field('who_description',$pageid); ?></p>
					<?php $button = get_field('who_button',$pageid); 
						if($button){ ?>
						<a href="<?php echo $button['url']; ?>" class="sl-btn"><?php echo $button['title']; ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div> -->
	<div class="home-interview">
		<div class="container">
			<div class="interview-title">
				<h4 class="title-tab"><?php echo get_field('interview_title',$pageid); ?> -></h4>
				<!-- <p class="text-center"><i><?php echo get_field('interview_description',$pageid); ?></i></p> -->
				<div class="pd-list list-flex">
					<?php
						$args = array(
							'cat' => 21, 
							'posts_per_page' => 3,
						);
						 $the_query = new WP_Query( $args );
						while ($the_query->have_posts() ) : $the_query->the_post();
						$author_id = get_post_field ('post_author', $post->ID);
						$author_name = get_the_author_meta( 'nickname' , $author_id ); 
						$author_url = get_author_posts_url( $author_id );
					?>
					<div class="pd-it animated fade-in">
						<div class="featured image-fit">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
						</div>
						<div class="info">
							<h4 class="type-style-overline"><a href="<?php echo get_term_link(21,'category'); ?>">Skincare</a></h4>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<h5 class="text-uppercase font-effra author-date img-tick"><a href="<?php echo $author_url;?>">
						<img src="<?php echo get_template_directory_uri() ?>/assets/images/tick.svg">
							BY <?php echo $author_name; echo " | "; echo get_the_date('F d, y');?></a></h5>
						</div>
					</div>
					<?php
						endwhile;
						wp_reset_query();
					?> 
				</div>
				<!-- <div class="text-center">
					<a class="btn-white" href="<?php echo  get_term_link(21,'category'); ?>">READ MORE</a>
				</div> -->
			</div>
		</div>
	</div>
	<div class="home-banner post">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 3, 
					'posts_per_page' => 2,
				);
				$the_query = new WP_Query( $args );
				$banner = $the_query->posts[1];
			?>
			<div class="hbanner-box">
				<div class="featured image-fit">
					<img src="<?php echo get_field('banner_image', $pageid) ?>" alt="">
				</div>
				<div class="info animated opacity-in">
				<?php 
					
					if($banner){
						$post_id = $banner->ID;
						$author_id = get_post_field('post_author', $post_id);
						$cat = get_the_category($banner->ID);
					?>
					<div class="info-banner animated opacity-in">
						<h4 class="text-uppercase sub-title font-effra"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
						<h4>
							<a href="<?php echo get_permalink($post_id); ?>">
							<span class="info-title">
								<?php echo $banner->post_title; ?>
							</span>
							<p class="info-content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Totam ipsam magni harum ut distinctio ducimus.</p>
								<a href="<?php echo get_author_posts_url( $author_id ); ?>" class="color-red">By <?php echo get_the_author_meta( 'nickname' , $author_id ); ?></a>
							</a>
						</h4>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<!-- <div class="home-banner home-banner-2">
		<div class="container">
			<div class="hbanner-box">
				<div class="info animated opacity-in">
					<h3><?php echo get_field('question_title',$pageid); ?></h3>
					<p><?php echo get_field('question_description',$pageid); ?></p>
				</div>
				<div class="chat text-center">
					<div class="chat-people list-flex">
						<?php $question_consultants = get_field('question_consultants',$pageid);
						if( $question_consultants){
							foreach ($question_consultants as $question) {
						?>
						<div class="chat-people-it">
							<div class="avata image-fit">
								<img src="<?php echo $question['avata']; ?>" alt="">
							</div>
							<h4 class="font-effra text-uppercase"><?php echo $question['name']; ?></h4>
						</div>
						<?php }} ?>
					</div>
					<?php $button = get_field('question_button_link',$pageid);
					if($button){ ?>
						<a href="<?php echo $button['url']; ?>" class="sl-btn"><?php echo $button['title']; ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div> -->
	<!-- <div class="home-interview">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 3, 
					'posts_per_page' => 6,
				);
				$the_query = new WP_Query( $args );
			?>
			<div class="interview-title">
				<h4 class="title-tab"><?php echo ucwords(str_replace('-', ' ',$the_query->query_vars['category_name'])); ?> -></h4>
				<p><i><?php echo get_field('aesthetic_description',$pageid); ?></i></p>
			</div>
			<div class="list-flex htop-box">
				<div class="left">
					<div class="big">
						<?php 
							$post_top = $the_query->posts[0];
							$post_top_id = $post_top->ID;
							$author_top_id = get_post_field ('post_author', $post_id);
							if($post_top){
						?>
						<div class="info info-pd">
							<?php $cat = get_the_category($post_top_id); ?>
							 <?php if(!empty($cat) && count($cat) > 0) :  ?>
								<h4 class="text-uppercase sub-title font-effra">
									<a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>">
										<?php echo $cat[0]->name; ?>
									</a>
								</h4>	
							<?php endif; ?>
							<h2><a href="<?php echo get_permalink($post_top_id); ?>"><?php echo $post_top->post_title; ?></a></h2>
							<p><?php echo get_the_excerpt($post_top_id); ?></p>
							<h5 class="text-uppercase font-effra author-date"><a href="<?php echo get_author_posts_url( $author_top_id ); ?>">by <?php echo get_the_author_meta( 'nickname' , $author_top_id ); ?></a><span class="pipe">|</span><?php echo get_the_date('F d, y',$post_top_id); ?></h5>
						</div>
						<?php } ?>
					</div>
				</div>

				<div class="right">
					<div class="latest-list">
						<?php
							while ($the_query->have_posts() ) : $the_query->the_post();
							$author_id = get_post_field ('post_author', $post->ID);
							$author_name = get_the_author_meta( 'nickname' , $author_id ); 
							$author_url = get_author_posts_url( $author_id );
						?>
						<div class="latest-it list-flex">
							<div class="featured image-fit">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
							</div>
							<div class="info animated fade-right">
								<?php $cat = get_the_category($post->ID);
									if(!empty($cat) && count($cat) > 0) :  ?>
									<h4 class="text-uppercase sub-title font-effra"><a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>"><?php echo $cat[0]->name; ?></a></h4>
								<?php endif; ?>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $post_author_url; ?>">by <?php echo $post_display_name; ?></a></h5>
							</div>
						</div>
						<?php
							endwhile;
							wp_reset_query();
						?>
					</div>
				</div>
			</div>
		</div>
	</div> -->
	<div class="home-top interview">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 3, 
					'posts_per_page' => 5,
				);
				$the_query = new WP_Query( $args );
				$post_top = $the_query->posts ? $the_query->posts[0] : null;
				if($post_top){
			?>
		<div class="interview-title">
			<h4 class="title-tab"><?php echo ucwords(str_replace('-', ' ',$the_query->query_vars['category_name'])); ?> -></h4>
			<!-- <p class="text-center"><i><?php echo get_field('aesthetic_description',$pageid); ?></i></p> -->
		</div>
		<div class="list-flex htop-box">
			<div class="right">
				<div class="latest-list">
					<?php
						while ($the_query->have_posts() ) : $the_query->the_post();
						if($post->ID != $post_top->ID){
						$post_author_id = get_post_field ('post_author', $post->ID);
						$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
						$post_author_url = get_author_posts_url( $post_author_id );
					?>
					<div class="latest-it list-flex">
						<div class="featured image-fit">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
						</div>
						<div class="info animated fade-right">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $post_author_url; ?>">by <?php echo $post_display_name; ?></a></h5>
						</div>
						<!-- <div class="line-border"></div> -->
					</div>
					<?php
						};
						endwhile;
						wp_reset_query();
					?>
				</div>
			</div>
			<div class="left">
				<div class="big">
					<?php 
						$post_top_id = $post_top->ID;
						$author_top_id = get_post_field ('post_author', $post_id);
						if($post_top){
					?>
					<div class="featured image-fit">
						<a href="<?php echo get_permalink($post_top_id); ?>"><img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post_top_id)); ?>" alt=""></a>
					</div>
					<div class="info info-pd">
						<?php $cat = get_the_category($post_top_id); ?>
						 <?php if(!empty($cat) && count($cat) > 0) :  ?>
							<h4 class="text-uppercase sub-title font-effra">
								<a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>">
									<?php echo $cat[0]->name; ?>
								</a>
							</h4>	
						<?php endif; ?>
						<h2><a href="<?php echo get_permalink($post_top_id); ?>"><?php echo $post_top->post_title; ?></a></h2>
						<h5 class="text-uppercase font-effra author-date"><a href="<?php echo get_author_posts_url( $author_top_id ); ?>">by <?php echo get_the_author_meta( 'nickname' , $author_top_id ); ?></a><span class="pipe">|</span><?php echo get_the_date('F d, y',$post_top_id); ?></h5>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
			<?php } ?>
		</div>
	</div>
	<!-- <div class="home-banner" style="background-color:#FBF2F3">
		<div class="container">
			<div class="hbanner-box">
				<div class="featured image-fit">
					<img src="<?php echo get_field('now_background',$pageid); ?>" alt="">
				</div>
				<div class="info animated opacity-in">
					<h3><?php echo get_field('now_title',$pageid); ?></h3>
					<p><?php echo get_field('now_description',$pageid); ?></p>
					<?php $button = get_field('now_button',$pageid);
					if($button){ ?>
						<a href="<?php echo $button['url']; ?>" class="sl-btn"><?php echo $button['title']; ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div> -->
	<!-- <div class="home-interview">
		<div class="container">
			<div class="interview-title">
				<h4 class="type-style-overline font-15"><?php echo get_field('skincare_title',$pageid); ?></h4>
				<p><i><?php echo get_field('skincare_description',$pageid); ?></i></p>
			</div>
			<div class="pd-list list-flex">
				<?php
					$args = array(
						'cat' => 15, 
						'posts_per_page' => 6,
					);
					 $the_query = new WP_Query( $args );
					while ($the_query->have_posts() ) : $the_query->the_post();
					$author_id = get_post_field ('post_author', $post->ID);
					$author_name = get_the_author_meta( 'nickname' , $author_id ); 
					$author_url = get_author_posts_url( $author_id );
				?>
				<div class="pd-it animated fade-in">
					<div class="featured image-fit">
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="<?php echo get_term_link(15,'category'); ?>">Skincare</a></h4>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $author_url;?>">BY <?php echo $author_name; ?></a><?php echo get_the_date('F d, y'); ?></h5>
					</div>
				</div>
				<?php
					endwhile;
					wp_reset_query();
				?> 
			</div>
			<div class="text-center">
				<a class="btn-white" href="<?php echo get_term_link(15,'category'); ?>">READ MORE</a>
			</div>
		</div>
	</div> -->
	<div class="home-top interview">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 15, 
					'posts_per_page' => 5,
				);
				$the_query = new WP_Query( $args );
				$post_top = $the_query->posts ? $the_query->posts[0] : null;
				if($post_top){
			?>
		<div class="interview-title">
			<h4 class="title-tab"><?php echo ucwords(str_replace('-', ' ',$the_query->query_vars['category_name'])); ?> -></h4>
			<!-- <p class="text-center"><i><?php echo get_field('aesthetic_description',$pageid); ?></i></p> -->
		</div>
		<div class="list-flex htop-box">
			<div class="left">
				<div class="big">
					<?php 
						$post_top_id = $post_top->ID;
						$author_top_id = get_post_field ('post_author', $post_id);
						if($post_top){
					?>
					<div class="featured image-fit">
						<a href="<?php echo get_permalink($post_top_id); ?>"><img src="<?php echo wp_get_attachment_url(get_post_thumbnail_id($post_top_id)); ?>" alt=""></a>
					</div>
					<div class="info info-pd">
						<?php $cat = get_the_category($post_top_id); ?>
						 <?php if(!empty($cat) && count($cat) > 0) :  ?>
							<h4 class="text-uppercase sub-title font-effra">
								<a href="<?php echo get_term_link($cat[0]->term_id,'category'); ?>">
									<?php echo $cat[0]->name; ?>
								</a>
							</h4>	
						<?php endif; ?>
						<h2><a href="<?php echo get_permalink($post_top_id); ?>"><?php echo $post_top->post_title; ?></a></h2>
						<h5 class="text-uppercase font-effra author-date"><a href="<?php echo get_author_posts_url( $author_top_id ); ?>">by <?php echo get_the_author_meta( 'nickname' , $author_top_id ); ?></a><span class="pipe">|</span><?php echo get_the_date('F d, y',$post_top_id); ?></h5>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="right">
				<div class="latest-list">
					<?php
						while ($the_query->have_posts() ) : $the_query->the_post();
						if($post->ID != $post_top->ID){
						$post_author_id = get_post_field ('post_author', $post->ID);
						$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
						$post_author_url = get_author_posts_url( $post_author_id );
					?>
					<div class="latest-it list-flex">
						<div class="featured image-fit">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
						</div>
						<div class="info animated fade-right">
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $post_author_url; ?>">by <?php echo $post_display_name; ?></a></h5>
						</div>
						<!-- <div class="line-border"></div> -->
					</div>
					<?php
						};
						endwhile;
						wp_reset_query();
					?>
				</div>
			</div>
		</div>
			<?php } ?>
		</div>
	</div>
	<div class="home-top interview">
		<div class="container">
			<?php 
				$args = array(
					'cat' => 21, 
					'posts_per_page' => 5,
				);
				$the_query = new WP_Query( $args );
				$post_top = $the_query->posts ? $the_query->posts[0] : null;
				if($post_top){
			?>
			<div class="interview-title">
				<h4 class="title-tab"><?php echo ucwords(str_replace('-', ' ',$the_query->query_vars['category_name'])); ?> -></h4>
				<!-- <p class="text-center"><i><?php echo get_field('aesthetic_description',$pageid); ?></i></p> -->
			</div>
			<div class="list-flex htop-box">
				<div class="right only-right">
					<div class="latest-list">
						<?php
							while ($the_query->have_posts() ) : $the_query->the_post();
							$post_author_id = get_post_field ('post_author', $post->ID);
							$post_display_name = get_the_author_meta( 'nickname' , $post_author_id ); 
							$post_author_url = get_author_posts_url( $post_author_id );
						?>
						<div class="latest-it list-flex">
							<div class="featured image-fit">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
							</div>
							<div class="info animated fade-right">
								<h4 class="type-style-overline"><a href="<?php echo get_term_link(21,'category'); ?>">Skincare</a></h4>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<h5 class="text-uppercase font-effra author-date"><a href="<?php echo $post_author_url; ?>">by <?php echo $post_display_name; ?></a></h5>
							</div>
							<!-- <div class="line-border"></div> -->
						</div>
						<?php
							endwhile;
							wp_reset_query();
						?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="home-interview">
		<div class="container">
			<div class="interview-title">
				<h4 class="title-tab"><?php echo get_field('interview_title',$pageid); ?> -></h4>
				<!-- <p class="text-center"><i><?php echo get_field('interview_description',$pageid); ?></i></p> -->
				<div class="pd-list list-flex">
					<?php
						$args = array(
							'cat' => 15, 
							'posts_per_page' => 3,
						);
						 $the_query = new WP_Query( $args );
						while ($the_query->have_posts() ) : $the_query->the_post();
						$author_id = get_post_field ('post_author', $post->ID);
						$author_name = get_the_author_meta( 'nickname' , $author_id ); 
						$author_url = get_author_posts_url( $author_id );
					?>
					<div class="pd-it animated fade-in">
						<div class="featured image-fit">
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>
						</div>
						<div class="info">
							<h4 class="type-style-overline"><a href="<?php echo get_term_link(21,'category'); ?>">Skincare</a></h4>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<h5 class="text-uppercase font-effra author-date img-tick"><a href="<?php echo $author_url;?>">
						<img src="<?php echo get_template_directory_uri() ?>/assets/images/tick.svg">
							BY <?php echo $author_name; echo " | "; echo get_the_date('F d, y');?></a></h5>
						</div>
					</div>
					<?php
						endwhile;
						wp_reset_query();
					?> 
				</div>
				<!-- <div class="text-center">
					<a class="btn-white" href="<?php echo  get_term_link(21,'category'); ?>">READ MORE</a>
				</div> -->
			</div>
		</div>
	</div>
</main>
<?php get_footer(); ?>
<script>
	jQuery(function($){
		$('.medical-slider').slick({
			infinite: false,
		  	slidesToShow: 5.5,
		  	slidesToScroll: 1,
		  	dots: false,
		  	arrows: true,
		  	 responsive: [
		    {
		      breakpoint: 1280,
		      settings: {
		        slidesToShow: 4.5,
		      }
		    },
		    {
		      breakpoint: 991,
		      settings: {
		        slidesToShow: 3.5,
		      }
		    },
		    {
		      breakpoint: 480,
		      settings: {
		        slidesToShow: 1,
		        slidesToScroll: 1
		      }
		    }
		  ]
		});
		$('.medical-slider').on('afterChange', function(event, slick, currentSlide) {
		  console.log(currentSlide);
		  var number = (slick.$slides.length-1)/5;
		  if (number == currentSlide) {
		    $('.home-medical .medical-list').addClass('active');
		  }
		  else{
		  	$('.home-medical .medical-list').removeClass('active');
		  }
		});
	})
</script>