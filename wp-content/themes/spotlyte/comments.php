<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments">

	<?php if ( have_comments() ) : ?>
		<ul>
			<?php
				wp_list_comments( array(
					'style'       => 'ul',
					'short_ping'  => true,
					'avatar_size' => 56,
				) );
			?>
		</ul><!-- .comment-list -->
	<?php endif; // have_comments() ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php _e( 'Bình luận đã đóng.', 'twentyfifteen' ); ?></p>
	<?php endif; ?>
	<hr />
	<div id="write-comment">
	<div class="section-header">
		<p id="add-comment-title" class="h1">Để lại bình luận</p>
	</div>
	<div class="grid">
		<div class="grid-item large--three-fifths push--large--one-fifth">
	<?php 
	$fields =  array(
	  'author' =>
		'<p class="comment-form-author">' .
		'<input placeholder="Tên" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
		'" size="30"' . $aria_req . ' /></p>',

	  'email' =>
		'<p class="comment-form-email">' .
		'<input placeholder="Email" id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
		'" size="30"' . $aria_req . ' /></p>',
	);
	$args = array(
	  'id_form'           => 'comment-form',
	  'class_form'      => 'comment-form',
	  'id_submit'         => '',
	  'class_submit'      => 'btn',
	  'name_submit'       => 'submit',
	  'title_reply'       => __( '' ),
	  'comment_notes_before'	=> '',
	  'title_reply_to'    => __( 'Để lại một bình luận tới %s' ),
	  'cancel_reply_link' => __( 'Hủy trả lời' ),
	  'label_submit'      => __( 'Đăng bình luận' ),
	  'format'            => 'xhtml',
	  'fields' => $fields,
	  'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Đăng nhập bởi <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>',
	  'comment_field' =>  '<p class="comment-form-comment"><textarea placeholder="Nội dung lời nhắn" id="comment" name="comment" aria-required="true">' .
		'</textarea></p>',
	);
	comment_form($args); ?>
			</div>
		</div>
	</div>

</div><!-- .comments-area -->
