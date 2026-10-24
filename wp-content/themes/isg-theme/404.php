<?php
/**
 * Not-found template.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main section-space">
	<div class="container container--content error-page">
		<p class="eyebrow"><?php echo esc_html( isg_theme_t( 'Error 404', 'Ошибка 404' ) ); ?></p>
		<h1 class="page-title"><?php echo esc_html( isg_theme_t( 'Page not found', 'Страница не найдена' ) ); ?></h1>
		<p><?php echo esc_html( isg_theme_t( 'The page may have moved or the address may be incorrect.', 'Возможно, страница была перемещена или адрес указан неверно.' ) ); ?></p>
		<?php get_search_form(); ?>
		<p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Back to home', 'На главную' ) ); ?></a></p>
	</div>
</main>
<?php
get_footer();
