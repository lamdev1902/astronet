<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<?php
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
		wp_head();
	?>
	<title>
		<?php
		global $page, $paged;
		wp_title( '|', true, 'right' );
		bloginfo( 'name' );
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );
		?>
	</title>
	
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<!-- Google Font -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo get_field('favicon','option'); ?>" />
</head>
<body <?php body_class(); ?>> 
<?php if ( is_front_page()) {  ?>
	<div class="top-logo">
		<div class="logo text-center">
			<a href="<?php echo home_url(); ?>">
				<img src="<?php echo get_field('logo_top','option'); ?>" alt="">
			</a>
		</div>
		<?php
			if ( is_active_sidebar( 'custom-header-widget' ) ) : ?>
			
			<div id="header-widget-area"  class="custom-search chw-widget-area widget-area inactive" role="complementary">
				<?php dynamic_sidebar( 'custom-header-widget' ); ?>
				<button class="close">
					<img src="<?php echo get_template_directory_uri() ?>/assets/images/x-10326.svg" alt="" class="">
				</button>
			</div>
			<ul class="header_right" >
				<li class="header_right-item">
					<button class="searchIcon">
						<svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
							<path d="M13.5 6C10.5 6 8 8.5 8 11.5c0 1.1.3 2.1.9 3l-3.4 3 1 1.1 3.4-2.9c1 .9 2.2 1.4 3.6 1.4 3 0 5.5-2.5 5.5-5.5C19 8.5 16.5 6 13.5 6zm0 9.5c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"></path>
						</svg>
					</button>
				</li>
				<li class="header_right-item">
					<a href="#" class="news">News Letter</a>
				</li>
			</ul>
			
		<?php endif; ?>
	</div>
<?php } ?>
<header id="header">
	<div class="hd-bg">
		<div class="container">
			<div class="hd-box list-flex flex-center">
				<!-- <?php if ( is_front_page()) { ?>
				<div class="social">
					<?php 
						$list_social = get_field('social', 'option');
						if($list_social){
							foreach($list_social as $social){
					?>
					<a href="<?php echo $social['link']; ?>" target="_blank"><i class="<?php echo $social['icon']; ?>"></i></a>
					<?php }} ?>
				</div>
				<?php } ?> -->
				<div class="header-logo <?php if ( is_front_page()) { echo ''; } else {echo 'show'; } ?>">
					<a href="<?php echo home_url(); ?>"><img src="<?php echo get_field('logo','option'); ?>" alt=""></a>
				</div>
				<div class="menu-bg">
					<div class="menu-main">
						<?php 
							wp_nav_menu(array(
								'theme_location' => 'menu_main',
							));
						?>

					</div>
					<div class="social-bg">
						<div class="divider divider__dark"></div>
						<div class="social text-center">
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
				<div class="provider-link inactive">
				<?php if ( is_active_sidebar( 'custom-header-widget' ) ) : ?>
			
					<div id="header-widget-area"  class="custom-search chw-widget-area widget-area mobile-header inactive" role="complementary">
						<?php dynamic_sidebar( 'custom-header-widget' ); ?>
						<button class="close">
							<img src="<?php echo get_template_directory_uri() ?>/assets/images/x-10326.svg" alt="" class="">
						</button>
					</div>
					<ul class="header_right" >
						<li class="header_right-item">
							<button class="searchIcon">
								<svg class="search-icon" viewBox="0 0 24 24" width="24" height="24">
									<path d="M13.5 6C10.5 6 8 8.5 8 11.5c0 1.1.3 2.1.9 3l-3.4 3 1 1.1 3.4-2.9c1 .9 2.2 1.4 3.6 1.4 3 0 5.5-2.5 5.5-5.5C19 8.5 16.5 6 13.5 6zm0 9.5c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"></path>
								</svg>
							</button>
						</li>
					</ul>
					
				<?php endif; ?>
				</div>
				<!-- <div class="provider-link">
					<a class="provider-link-btn text-uppercase" href="<?php echo get_field('provider_link','option'); ?>">
						<?php echo get_field('provider_title','option'); ?>
					</a>
					<div class="provider-disclaimer">
						<p><?php echo get_field('provider_description','option'); ?></p>
						<a class="sl-btn" href="<?php echo get_field('provider_link','option'); ?>">search</a>
					</div>
				</div> -->
				<button class="toogle-menu" type="button">
					<span></span>
				</button>
			</div>
		</div>
	</div>
</header>