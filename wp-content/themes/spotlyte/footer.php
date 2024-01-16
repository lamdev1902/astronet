<footer id="footer">
	<div class="container">
		<div class="ft-top list-flex flex-center">
			<div class="left">
				<div class="ft-logo">
					<a href="<?php echo home_url(); ?>">
						<img src="<?php echo get_field('logo_footer','option'); ?>" alt="">
					</a>
				</div>
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
			<div class="newsletter">
				<form action="">
					<label>sign up for our newsletter</label>
					<div class="newsletter-box list-flex">
						<input class="input-email" type="email" value="" placeholder="email address">
						<div class="email-btn">
							<input class="email-submit" type="submit" value="sign up">
						</div>
					</div>
				</form>
			</div>
			<div class="right">
				<div class="ft-menu">
					<?php 
						wp_nav_menu(array(
							'theme_location' => 'menu_footer',
						));
					?>
				</div>
			</div>
		</div>
		<div class="ft-bottom list-flex flex-center">
			<span class="divider divider__light"></span>
			<p class="copyright"><?php echo get_field('copyright','option'); ?></p>
			<div class="newsletter">
				<form action="">
					<label>sign up for our newsletter</label>
					<div class="newsletter-box list-flex">
						<input class="input-email" type="email" value="" placeholder="email address">
						<div class="email-btn">
							<input class="email-submit" type="submit" value="sign up">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</footer>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/jquery-3.5.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/slick/slick.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/animate/animate.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/custom.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/age-calculate.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/bodyfat-calculate.js"></script>
<?php wp_footer();?>
</body>
</html>