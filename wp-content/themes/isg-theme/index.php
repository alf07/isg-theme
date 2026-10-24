<?php
/**
 * Main fallback template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main section-space">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title"><?php echo is_home() && (int) get_option( 'page_for_posts' ) ? esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ) : esc_html( isg_theme_t( 'Posts', 'Публикации' ) ); ?></h1>
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
