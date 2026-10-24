<?php
/**
 * Archive template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main section-space">
	<div class="container">
		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="archive-description prose">', '</div>' ); ?>
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
