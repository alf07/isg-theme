<?php
/**
 * Page content template part.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'entry entry--page' ); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>
	<?php get_template_part( 'template-parts/content/demo-badge' ); ?>
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
	<?php if ( '1' === get_post_meta( get_the_ID(), 'isg_demo_content', true ) ) : ?>
		<p class="isg-demo-notice"><strong><?php echo esc_html( isg_theme_t( 'Demo content.', 'Демо-контент.' ) ); ?></strong> <?php echo esc_html( get_post_meta( get_the_ID(), 'isg_demo_note', true ) ?: isg_theme_t( 'Replace this text with approved material before publication.', 'Замените текст утверждёнными материалами перед публикацией.' ) ); ?></p>
	<?php endif; ?>
</article>
