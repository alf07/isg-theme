<?php
/**
 * Empty state for listings and search results.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="empty-state">
	<h2><?php echo esc_html( isg_theme_t( 'Nothing found', 'Ничего не найдено' ) ); ?></h2>
	<p><?php echo esc_html( isg_theme_t( 'Try another search or return to the main page.', 'Попробуйте изменить запрос или вернитесь на главную страницу.' ) ); ?></p>
	<?php get_search_form(); ?>
</section>
