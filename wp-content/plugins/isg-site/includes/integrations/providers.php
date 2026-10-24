<?php
/** Integration boundaries: demo providers intentionally perform no external calls. */
defined( 'ABSPATH' ) || exit;
function isg_site_demo_integration( string $provider, array $payload = array() ): array {
    $allowed = array( 'payment', 'ai_assistant', 'telegram', 'digital_delivery' );
    if ( ! in_array( $provider, $allowed, true ) ) {
        return array( 'ok' => false, 'status' => 'unsupported_provider', 'message' => 'Неизвестный демонстрационный провайдер.' );
    }
    $reference = strtoupper( substr( hash( 'sha256', $provider . wp_json_encode( $payload ) . wp_rand() ), 0, 10 ) );
    return array(
        'ok' => true,
        'demo' => true,
        'provider' => $provider,
        'reference' => $reference,
        'message' => 'Демонстрационный сценарий выполнен локально. Внешний сервис не вызывался, деньги и сообщения не отправлялись.',
        'payload_keys' => array_keys( $payload ),
    );
}
