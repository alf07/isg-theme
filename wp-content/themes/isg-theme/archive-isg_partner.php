<?php
/** Archive template for ISG custom content. */
defined( 'ABSPATH' ) || exit;
get_header();
$post_type = get_post_type();
$labels = array( 'isg_solution' => isg_theme_t( 'ISG solutions', 'Решения ISG' ), 'isg_service' => isg_theme_t( 'ISG services', 'Услуги ISG' ), 'isg_vacancy' => isg_theme_t( 'Careers at ISG', 'Работа в ISG' ), 'isg_partner' => isg_theme_t( 'Partners and implementation examples', 'Партнёры и примеры внедрения' ) );
$title = $labels[ $post_type ] ?? get_the_archive_title();
?>
<main id="main-content" class="site-main isg-directory"><div class="container">
<nav class="isg-breadcrumbs" aria-label="Хлебные крошки"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( isg_theme_t( 'Home', 'Главная' ) ); ?></a><span aria-hidden="true">/</span><span><?php echo esc_html( $title ); ?></span></nav>
<header class="isg-directory__heading"><p class="eyebrow">Inform Systems Group</p><h1><?php echo esc_html( $title ); ?></h1><p><?php echo esc_html( isg_theme_t( 'Company information and offerings. Content is managed in the WordPress admin area.', 'Материалы и предложения компании. Содержание управляется из административной панели WordPress.' ) ); ?></p></header>
<?php if ( have_posts() ) : ?><div class="isg-directory__grid"><?php while ( have_posts() ) : the_post();
$subtitle = get_post_meta( get_the_ID(), 'isg_subtitle', true );
$cta_label = get_post_meta( get_the_ID(), 'isg_cta_label', true );
$cta_url = isg_theme_internal_url_or_empty( (string) get_post_meta( get_the_ID(), 'isg_cta_url', true ) );
?>
<article <?php post_class( 'isg-entry-card' ); ?>>
<a class="isg-entry-card__image" href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'isg-theme-card' ); } else { echo '<span aria-hidden="true">ISG</span>'; } ?></a>
<div class="isg-entry-card__body"><?php get_template_part( 'template-parts/content/demo-badge' ); ?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
<?php if ( $subtitle ) : ?><p class="isg-entry-card__subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
<p><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ), 28 ) ); ?></p>
<a class="text-link" href="<?php echo esc_url( $cta_url ?: get_permalink() ); ?>"><?php echo esc_html( $cta_label ?: isg_theme_t( 'Learn more', 'Подробнее' ) ); ?> <span aria-hidden="true">↗</span></a></div></article>
<?php endwhile; ?></div><?php the_posts_pagination(); ?><?php else : ?><div class="isg-empty-state"><h2><?php echo esc_html( isg_theme_t( 'No content has been added yet', 'Материалы пока не добавлены' ) ); ?></h2><p><?php echo esc_html( isg_theme_t( 'Add entries in the administration panel and they will appear in this section.', 'Добавьте записи в административной панели, и они появятся в этом разделе.' ) ); ?></p></div><?php endif; ?>
</div></main>
<?php get_footer(); ?>
