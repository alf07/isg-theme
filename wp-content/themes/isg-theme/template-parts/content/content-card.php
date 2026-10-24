<?php
/**
 * Generic post card for archives and search results.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'content-card' ); ?>><?php get_template_part( 'template-parts/content/demo-badge' ); ?>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="content-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'isg-theme-card', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="content-card__body">
		<div class="entry-meta">
			<?php isg_theme_posted_on(); ?>
		</div>
		<h2 class="content-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="content-card__excerpt"><?php the_excerpt(); ?></div>
		<a class="text-link" href="<?php the_permalink(); ?>">
			<?php echo esc_html( isg_theme_t( 'Read more', 'Читать далее' ) ); ?>
			<span aria-hidden="true">&rarr;</span>
		</a>
	</div>
</article>
