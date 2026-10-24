<?php
/**
 * Search results template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main section-space">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title">
				<?php
			printf(
				esc_html( isg_theme_t( 'Search results for: %s', 'Результаты поиска: %s' ) ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
			</h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="content-grid">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content', 'card' ); ?>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
