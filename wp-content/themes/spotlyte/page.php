<?php 
$pageid = get_the_ID();
get_header();
the_post(); 
?>
<main id="content">
  <div class="privacy-custom">
    <div class="container">
      <div class="box">
        <?php the_content(); ?>
      </div>
    </div>
  </div>
</main>
<?php get_footer(); ?>