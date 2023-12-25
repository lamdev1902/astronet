<?php 
/* Template Name: About */
$pageid = get_the_ID();
get_header(); 
the_post();
?>
<main id="content">
	<div class="ab-top">
		<div class="container">
			<div class="box">
				<div class="featured image-fit">
					<?php the_post_thumbnail(); ?>
				</div>
				<div class="info text-center">
					<h1><?php the_title(); ?></h1>
					<?php echo get_field('short_introduction', $pageid); ?>
					<i class="far fa-long-arrow-down"></i>
				</div>
			</div>
		</div>
	</div>
	<div class="ab-custom bg-light">
		<div class="container">
			<div class="box">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
	<div class="ab-press home-interview">
		<div class="container">
			<div class="interview-title">
				<h4 class="type-style-overline font-15">HOT OFF THE PRESS</h4>
				<p><i>More moments from inspiring experts</i></p>
			</div>
			<div class="pd-list list-flex">
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image1.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Skincare</a></h4>
						<h3><a href="#">Vitiligo Is More Common Than You Think — Here’s What You Should Know</a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Gabby Shacknai</a>September 16, 2020</h5>
					</div>
				</div>
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image2.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Sunscreen</a></h4>
						<h3><a href="#">5 SPF-Infused Primers That Make It Easy For the Laziest Among Us to Wear Sunscreen</a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Emily Orofino</a>September 1, 2020</h5>
					</div>
				</div>
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image3.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Skincare</a></h4>
						<h3><a href="#">If You Love Vitamin C, You’ll Want to Tap into the Benefits of Vitamin B</a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Sophie Wirt</a>August 30, 2020</h5>
					</div>
				</div>
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image4.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Skincare</a></h4>
						<h3><a href="#">How to Address Tired-Looking Eyes, Crow’s Feet, and Dark Circles</a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Jessica Prince Erlich</a>September 16, 2020</h5>
					</div>
				</div>
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image5.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Skincare</a></h4>
						<h3><a href="#">Tricks and Tips For Getting Rid of Milia For Good </a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Wendy Rose Gould</a>September 16, 2020</h5>
					</div>
				</div>
				<div class="pd-it animated fade-in fadeInUp">
					<div class="featured image-fit">
						<a href="#"><img src="assets/images/about/image6.jpg" alt=""></a>
					</div>
					<div class="info">
						<h4 class="type-style-overline font-15"><a href="#">Skincare</a></h4>
						<h3><a href="#">Low-Fat Milk, Cool Showers & More Tips to Help Recover From a Sunburn</a></h3>
						<h5 class="text-uppercase font-effra author-date"><a href="#">by Wendy Rose Gould</a>September 16, 2020</h5>
					</div>
				</div>
			</div>
			<div class="text-center">
				<a class="btn-white" href="#">READ MORE</a>
			</div>
		</div>
	</div>
</main>
<?php get_footer(); ?>