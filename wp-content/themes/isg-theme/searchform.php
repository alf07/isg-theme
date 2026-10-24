<?php
/**
 * Search form.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="search-form__label">
		<span class="screen-reader-text"><?php echo esc_html( isg_theme_t( 'Search for:', 'Поиск по сайту:' ) ); ?></span>
		<input type="search" class="search-form__field" placeholder="<?php echo esc_attr( isg_theme_t( 'Search…', 'Поиск…' ) ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	</label>
	<button type="submit" class="search-form__submit"><?php echo esc_html( isg_theme_t( 'Search', 'Найти' ) ); ?></button>
</form>
