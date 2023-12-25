<?php 
/* Template Name: Contact */
$pageid = get_the_ID();
get_header(); 
the_post();
?>
<main id="content">
	<div class="contact-main">
		<div class="container">
			<div class="box">
				<h1><?php the_title(); ?></h1>
				<div class="contact-custom">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>
</main>
<?php get_footer(); ?>