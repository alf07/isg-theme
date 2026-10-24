<?php
/** Admin-only integration demonstration screen. */
defined( 'ABSPATH' ) || exit;
add_action( 'admin_menu', 'isg_site_register_integration_demo_page' );
function isg_site_register_integration_demo_page(): void {
    add_menu_page( 'Интеграции ISG', 'Интеграции ISG', 'manage_options', 'isg-integrations-demo', 'isg_site_render_integrations_demo_page', 'dashicons-randomize', 58 );
}
function isg_site_render_integrations_demo_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $result = null;
    if ( isset( $_POST['isg_demo_action'], $_POST['isg_demo_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['isg_demo_nonce'] ) ), 'isg_run_demo_integration' ) ) {
        $action = sanitize_key( wp_unslash( $_POST['isg_demo_action'] ) );
        $result = isg_site_demo_integration( $action, array( 'source' => 'wp-admin-demo', 'timestamp' => current_time( 'mysql' ) ) );
    }
    echo '<div class="wrap"><h1>Демонстрация интеграций</h1><p>Все кнопки запускают локальные заглушки. Реальные платежи, Telegram-сообщения и запросы к AI не выполняются.</p>';
    if ( is_array( $result ) ) { echo '<div class="notice notice-info"><p><strong>' . esc_html( $result['reference'] ?? 'DEMO' ) . '</strong> — ' . esc_html( $result['message'] ?? '' ) . '</p><pre>' . esc_html( wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) . '</pre></div>'; }
    $actions = array( 'payment' => 'Проверить сценарий оплаты', 'digital_delivery' => 'Проверить цифровую выдачу', 'ai_assistant' => 'Проверить AI-помощника', 'telegram' => 'Проверить передачу в Telegram' );
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;max-width:1000px">';
    foreach ( $actions as $key => $label ) { echo '<form method="post" style="padding:18px;border:1px solid #c3c4c7;background:#fff"><h2>' . esc_html( $label ) . '</h2><p>Демонстрационный ответ с идентификатором операции.</p>'; wp_nonce_field( 'isg_run_demo_integration', 'isg_demo_nonce' ); echo '<input type="hidden" name="isg_demo_action" value="' . esc_attr( $key ) . '"><button class="button button-primary">Запустить заглушку</button></form>'; }
    echo '</div><h2>Контракт интеграции</h2><p>Сценарии возвращают структурированный результат: <code>ok</code>, <code>demo</code>, <code>provider</code>, <code>reference</code>, <code>message</code>. В реальном подключении этот контракт можно сохранить, заменив внутреннюю реализацию провайдером.</p></div>';
}
