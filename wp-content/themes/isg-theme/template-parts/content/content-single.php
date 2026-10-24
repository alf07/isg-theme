<?php
/**
 * Single post content template part.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'entry entry--single' ); ?>><?php get_template_part( 'template-parts/content/demo-badge' ); ?>
	<header class="entry-header">
		<div class="entry-meta">
			<?php isg_theme_posted_on(); ?>
			<span aria-hidden="true">&middot;</span>
			<?php isg_theme_posted_by(); ?>
		</div>
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-featured-image">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
	<?php endif; ?>
	<div class="entry-content prose">
		<?php
		the_content();
		wp_link_pages( array(
			'before' => '<nav class="page-links" aria-label="' . esc_attr( isg_theme_t( 'Page sections', 'Разделы страницы' ) ) . '">',
			'after'  => '</nav>',
		) );
		?>
	</div>
	<footer class="entry-footer">
		<?php the_tags( '<div class="entry-tags">', ' ', '</div>' ); ?>
	</footer>
</article>
