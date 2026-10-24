<?php
/**
 * Comments template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
			printf(
				esc_html( isg_theme_t( 1 === get_comments_number() ? '%1$s comment' : '%1$s comments', 1 === get_comments_number() ? '%1$s комментарий' : '%1$s комментариев' ) ),
				number_format_i18n( get_comments_number() )
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 40,
			) );
			?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php echo esc_html( isg_theme_t( 'Comments are closed.', 'Комментарии закрыты.' ) ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
