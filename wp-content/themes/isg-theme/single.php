<?php
/**
 * Single post template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main section-space">
	<div class="container container--content">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/content/content', 'single' ); ?>
			<?php if ( comments_open() || get_comments_number() ) : ?>
				<?php comments_template(); ?>
			<?php endif; ?>
			<nav class="post-navigation" aria-label="<?php echo esc_attr( isg_theme_t( 'Post navigation', 'Навигация по публикациям' ) ); ?>">
				<div><?php previous_post_link( '%link', '&larr; %title' ); ?></div>
				<div><?php next_post_link( '%link', '%title &rarr;' ); ?></div>
			</nav>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
