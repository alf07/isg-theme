<?php
/**
 * Theme footer.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer" id="contact">
	<div class="container site-footer__top">
		<div class="site-footer__brand">
			<a class="site-branding__name site-branding__name--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="site-branding__wordmark"><span class="site-branding__isg">ISG</span><span class="site-branding__dev">.DEV</span></span>
				<span class="site-branding__descriptor">INFORM SYSTEMS GROUP</span>
			</a>
			<p class="site-footer__motto"><?php echo esc_html( isg_theme_t( (string) get_option( 'isg_footer_motto', 'Innovation for life.' ), 'Инновации для жизни.' ) ); ?></p>
		</div>
		<div class="site-footer__column"><h2><?php echo esc_html( isg_theme_t( 'Explore', 'Разделы' ) ); ?></h2><a href="<?php echo esc_url( ( get_post_type_archive_link( 'isg_solution' ) ?: home_url( '/solutions/' ) ) ); ?>"><?php echo esc_html( isg_theme_t( 'Products', 'Продукты' ) ); ?></a><a href="<?php echo esc_url( ( get_post_type_archive_link( 'isg_service' ) ?: home_url( '/services/' ) ) ); ?>"><?php echo esc_html( isg_theme_t( 'Services', 'Услуги' ) ); ?></a><a href="<?php echo esc_url( home_url( '/#industries' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Industries', 'Сферы' ) ); ?></a><a href="<?php echo esc_url( ( isg_theme_shop_url() ) ); ?>"><?php echo esc_html( isg_theme_t( 'Shop', 'Купить' ) ); ?></a></div>
		<div class="site-footer__column"><h2><?php echo esc_html( isg_theme_t( 'Company', 'Компания' ) ); ?></h2><a href="<?php echo esc_url( isg_theme_localized_page_url( 'company', '/company/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'About us', 'О компании' ) ); ?></a><a href="<?php echo esc_url( ( get_post_type_archive_link( 'isg_vacancy' ) ?: home_url( '/careers/' ) ) ); ?>"><?php echo esc_html( isg_theme_t( 'Careers', 'Работа' ) ); ?></a><a href="<?php echo esc_url( ( get_post_type_archive_link( 'isg_partner' ) ?: home_url( '/cases/' ) ) ); ?>"><?php echo esc_html( isg_theme_t( 'Partnership', 'Партнёрство' ) ); ?></a><a href="<?php echo esc_url( isg_theme_localized_page_url( 'contacts', '/contacts/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Contact', 'Контакты' ) ); ?></a></div>
		<div class="site-footer__contact"><h2><?php echo esc_html( isg_theme_t( 'Get in touch', 'Свяжитесь с нами' ) ); ?></h2><p class="site-footer__test-contact"><?php echo esc_html( isg_theme_t( 'Contact details and external channels are not connected in this test version.', 'Контакты и внешние каналы не подключены в тестовой версии.' ) ); ?></p><p><?php echo esc_html( isg_theme_t( 'Software, devices and integration solutions.', 'Программное обеспечение, устройства и интеграционные решения.' ) ); ?></p></div>
	</div>
	<div class="container site-footer__bottom">
		<p class="site-footer__test-note"><?php echo esc_html( isg_theme_t( (string) get_option( 'isg_footer_copyright', 'Test assignment · author Roman Litvinenko · demo version' ), 'Тестовое задание · автор Роман Литвиненко · демонстрационная версия' ) ); ?></p>
		<div class="site-footer__legal">
			<?php
			$privacy_id  = (int) get_option( 'wp_page_for_privacy_policy' );
			$privacy_url = '';
			if ( $privacy_id ) {
				$localized_privacy_id = isg_theme_localized_post_id( $privacy_id );
				if ( $localized_privacy_id ) { $privacy_url = (string) get_permalink( $localized_privacy_id ); }
			} else {
				$privacy_url = isg_theme_localized_page_url( 'privacy-policy', '/privacy-policy/' );
			}
			if ( $privacy_url ) :
			?>
			<a href="<?php echo esc_url( $privacy_url ); ?>"><?php echo esc_html( isg_theme_t( 'Privacy policy', 'Политика конфиденциальности' ) ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( isg_theme_localized_page_url( 'sitemap', '/sitemap/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Sitemap', 'Карта сайта' ) ); ?></a>
		</div>
		<p class="site-footer__signature">INNOVATION <span>+</span> LIFE</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
