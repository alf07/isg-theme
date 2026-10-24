<?php
/** Single template for ISG custom content. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main isg-single"><div class="container">
<?php while ( have_posts() ) : the_post();
$post_type = get_post_type();
$labels = array( 'isg_solution' => isg_theme_t( 'Solution', 'Решение' ), 'isg_service' => isg_theme_t( 'Service', 'Услуга' ), 'isg_vacancy' => isg_theme_t( 'Vacancy', 'Вакансия' ), 'isg_partner' => isg_theme_t( 'Partner / implementation example', 'Партнёр / пример внедрения' ) );
$subtitle = get_post_meta( get_the_ID(), 'isg_subtitle', true );
$cta_label = get_post_meta( get_the_ID(), 'isg_cta_label', true );
$cta_url = isg_theme_internal_url_or_empty( (string) get_post_meta( get_the_ID(), 'isg_cta_url', true ) );
$demo_url = isg_theme_internal_url_or_empty( (string) get_post_meta( get_the_ID(), 'isg_demo_url', true ) );
$price_note = get_post_meta( get_the_ID(), 'isg_price_note', true );
?>
<nav class="isg-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Home', 'Главная' ) ); ?></a><span aria-hidden="true">/</span><a href="<?php echo esc_url( get_post_type_archive_link( $post_type ) ); ?>"><?php echo esc_html( $labels[ $post_type ] ?? isg_theme_t( 'Content', 'Материалы' ) ); ?></a><span aria-hidden="true">/</span><span><?php the_title(); ?></span></nav>
<article <?php post_class( 'isg-single__article' ); ?>><header class="isg-single__heading"><p class="eyebrow"><?php echo esc_html( $labels[ $post_type ] ?? isg_theme_t( 'ISG content', 'Материал ISG' ) ); ?></p><h1><?php the_title(); ?></h1><?php get_template_part( 'template-parts/content/demo-badge' ); ?><?php if ( $subtitle ) : ?><p class="isg-single__subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?></header>
<?php if ( has_post_thumbnail() ) : ?><figure class="isg-single__image"><?php the_post_thumbnail( 'large' ); ?></figure><?php endif; ?>
<div class="isg-single__content entry-content"><?php the_content(); ?></div><?php if ( '1' === get_post_meta( get_the_ID(), 'isg_demo_content', true ) ) : ?><p class="isg-demo-notice"><strong><?php echo esc_html( isg_theme_t( 'Demo content.', 'Демо-контент.' ) ); ?></strong> <?php echo esc_html( get_post_meta( get_the_ID(), 'isg_demo_note', true ) ?: isg_theme_t( 'Replace this text with approved information before publication.', 'Замените текст согласованной информацией перед публикацией.' ) ); ?></p><?php endif; ?>
<?php if ( $price_note ) : ?><p class="isg-single__price-note"><?php echo esc_html( $price_note ); ?></p><?php endif; ?>
<div class="isg-single__actions"><?php if ( $cta_url ) : ?><a class="button button--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ?: isg_theme_t( 'Contact us', 'Связаться' ) ); ?> ↗</a><?php endif; ?><?php if ( $demo_url ) : ?><a class="button button--outline" href="<?php echo esc_url( $demo_url ); ?>"><?php echo esc_html( isg_theme_t( 'Demo / materials ↗', 'Демо / материалы ↗' ) ); ?></a><?php endif; ?><?php if ( 'isg_vacancy' === $post_type ) : ?><a class="button button--primary" href="#contact"><?php echo esc_html( isg_theme_t( 'Apply', 'Откликнуться' ) ); ?></a><?php endif; ?></div>
</article><?php endwhile; ?>
</div></main>
<?php get_footer(); ?>
