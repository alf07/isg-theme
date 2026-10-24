<?php
/** Visible, non-deceptive label for demo data. */
defined( 'ABSPATH' ) || exit;
if ( '1' === get_post_meta( get_the_ID(), 'isg_demo_content', true ) ) :
?>
<span class="isg-demo-badge" title="Демо-наполнение — заменить согласованными данными"><?php echo esc_html( isg_theme_t( 'DEMO · example', 'ДЕМО · пример' ) ); ?></span>
<?php endif; ?>
