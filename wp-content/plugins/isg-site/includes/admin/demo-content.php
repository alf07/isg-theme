<?php
/**
 * Demo content installer for the review environment.
 * Creates editable WordPress content and WooCommerce catalog items without overwriting existing records.
 * All seeded records are marked with isg_demo_content=1 and visibly labeled as demo data.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'isg_site_register_demo_content_page' );
add_action( 'admin_post_isg_site_seed_demo', 'isg_site_handle_seed_demo' );
add_action( 'admin_init', 'isg_site_repair_shop_page_assignment', 30 );
add_shortcode( 'isg_sitemap', 'isg_site_render_sitemap' );

function isg_site_register_demo_content_page(): void {
    add_menu_page( 'ISG — управление сайтом', 'ISG Site', 'manage_options', 'isg-demo-content', 'isg_site_render_demo_content_page', 'dashicons-screenoptions', 58 );
}

function isg_site_render_demo_content_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $types = array(
        'post' => 'Новости',
        'isg_solution' => 'Решения',
        'isg_service' => 'Услуги',
        'isg_vacancy' => 'Вакансии (карточки-примеры)',
        'isg_partner' => 'Кейсы-примеры',
        'product' => 'Товары WooCommerce',
        'page' => 'Служебные страницы',
    );
    echo '<div class="wrap isg-admin-wrap"><h1>ISG Site — демо-наполнение</h1><p class="description">Однократный запуск создаёт недостающие записи для демонстрации темы, архива и админки. Создаются английская и русская версии материалов; при активном Polylang/WPML они связываются как переводы. Существующие записи не перезаписываются. У каждого созданного материала стоит метка <code>isg_demo_content</code>; она показывается в публичной части сайта.</p>';
    if ( ! isg_site_demo_languages_configured() ) {
        echo '<div class="notice notice-warning"><p><strong>Внимание:</strong> перед публикацией настрой English (slug <code>en</code>) и Russian (slug <code>ru</code>) в Polylang или соответствующие языки в WPML. Генератор создаст варианты EN/RU, но без мультиязычного плагина или без обоих языков они не смогут работать как связанные переводы и языковые URL.</p></div>';
    } else {
        echo '<div class="notice notice-success"><p><strong>EN + RU:</strong> оба языка обнаружены. Новые демо-материалы будут создаваться парами и связываться как переводы.</p></div>';
    }
    if ( isset( $_GET['isg_seeded'] ) ) {
        $created = absint( wp_unslash( $_GET['isg_seeded'] ) );
        echo '<div class="notice notice-success is-dismissible"><p>Обработка завершена. Создано новых записей: <strong>' . esc_html( (string) $created ) . '</strong>. Повторный запуск безопасен: существующие записи пропускаются.</p></div>';
    }
    echo '<div style="max-width:980px;background:#fff;border:1px solid #dcdcde;padding:22px;margin:20px 0;">';
    echo '<h2>Содержимое сайта</h2><table class="widefat striped" style="max-width:740px"><thead><tr><th>Тип</th><th>Опубликовано</th><th>Демо-записей</th></tr></thead><tbody>';
    foreach ( $types as $type => $label ) {
        $all = wp_count_posts( $type );
        $published = $all && isset( $all->publish ) ? (int) $all->publish : 0;
        $query = new WP_Query( array( 'post_type' => $type, 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => 'isg_demo_content', 'meta_value' => '1', 'no_found_rows' => false ) );
        printf( '<tr><td>%s</td><td>%s</td><td>%s</td></tr>', esc_html( $label ), esc_html( (string) $published ), esc_html( (string) $query->found_posts ) );
        wp_reset_postdata();
    }
    echo '</tbody></table>';
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<p><strong>WooCommerce не активирован.</strong> Записи услуг/решений и страницы будут созданы, но тестовые товары появятся после установки и активации WooCommerce и повторного запуска.</p>';
    } else {
        echo '<p>WooCommerce активен. Тестовые товары будут без цен и не смогут оформлять заказ: ценовые условия и подключение оплат не выдумываются.</p>';
    }
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:20px">';
    wp_nonce_field( 'isg_site_seed_demo' );
    echo '<input type="hidden" name="action" value="isg_site_seed_demo"><button class="button button-primary button-hero" type="submit">Создать недостающий демо-контент</button></form>';
    echo '<p style="color:#646970;margin-top:16px">После проверки удалите записи с меткой «ДЕМО» или замените их утверждённым контентом. Кнопка не запускает платёжные, почтовые, AI- или Telegram-интеграции.</p></div>';
    echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=isg-language-setup' ) ) . '">Настройка мультиязычности</a> <a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener noreferrer">Открыть сайт ↗</a> <a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=isg_solution' ) ) . '">Управлять решениями</a> <a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=isg_service' ) ) . '">Управлять услугами</a></p></div>';
}

function isg_site_handle_seed_demo(): void {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Недостаточно прав.' ); }
    check_admin_referer( 'isg_site_seed_demo' );
    $created = isg_site_seed_demo_content();
    wp_safe_redirect( add_query_arg( 'isg_seeded', absint( $created ), admin_url( 'admin.php?page=isg-demo-content' ) ) );
    exit;
}

function isg_site_seed_demo_content(): int {
    $created = 0;
    $solution_groups = array(
        'software' => 'Software products', 'finance' => 'Financial systems',
        'devices' => 'Devices and self-service', 'infrastructure' => 'Infrastructure',
    );
    foreach ( $solution_groups as $slug => $name ) { isg_site_ensure_term( 'isg_solution_group', $slug, $name ); }
    $service_groups = array(
        'web3' => 'Web3 services', 'skysend' => 'SkySend services', 'development' => 'Software development',
        'analytics' => 'Analytics', 'implementation' => 'Implementation',
    );
    foreach ( $service_groups as $slug => $name ) { isg_site_ensure_term( 'isg_service_group', $slug, $name ); }

    $solutions = array(
        array( 'allvend', 'ALLVEND', 'Self-service device software platform', 'DEMO content. ALLVEND brings together user workflows for kiosks and automated devices. Capabilities depend on configuration, peripherals and connected providers. Use this record to review WordPress content management.', 'software', 'Explore ALLVEND', '#allvend' ),
        array( 'skysend', 'SkySend', 'Clustered payment processing system', 'DEMO content. Example SkySend product description covering connected locations, transaction processing, monitoring, balances and reporting. Confirm specific functions and parameters for the actual delivery.', 'finance', 'Explore SkySend', '#skysend' ),
        array( 'fastsys', 'FastSYS', 'Linux-based embedded operating system', 'DEMO content for FastSYS on resource-constrained devices. Confirm supported configurations, delivery model and the specifications of the relevant version before publication.', 'software', 'Explore FastSYS', '#fastsys' ),
        array( 'finger', 'FINGER', 'Biometric components and identification workflows', 'DEMO description of the FINGER product area. Add the intended use, supported devices, architecture, data requirements and usage restrictions after approval by the product owner.', 'devices', 'Request information', '' ),
        array( 'beauty-kiosk', 'Beauty kiosks', 'Interactive devices and self-service kiosks', 'DEMO hardware record. Add approved device imagery, available options, interfaces and technical characteristics from the current specification.', 'devices', 'Request specifications', '' ),
        array( 'smart-city', 'Smart City', 'Integrated urban digital infrastructure solutions', 'DEMO description for system integration, workflow automation and interaction with city services. The scope of an actual project is determined after discovery.', 'infrastructure', 'Discuss a project', '' ),
    );
    foreach ( $solutions as $row ) {
        $id = isg_site_seed_post( 'isg_solution', $row[0], $row[1], $row[3], $row[2], $row[6], 'DEMO content. Replace with approved solution information before publication.' );
        $was_created = $id ? isg_site_last_seed_created() : 0;
        $created += $was_created;
        if ( $id ) {
            $assigned_terms = wp_get_object_terms( $id, 'isg_solution_group', array( 'fields' => 'ids' ) );
            if ( $was_created || ( ! is_wp_error( $assigned_terms ) && empty( $assigned_terms ) ) ) { wp_set_object_terms( $id, array( $row[4] ), 'isg_solution_group', false ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_subtitle' ) ) { update_post_meta( $id, 'isg_subtitle', $row[2] ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_cta_label' ) ) { update_post_meta( $id, 'isg_cta_label', $row[5] ); }
            if ( $row[6] && ( $was_created || ! metadata_exists( 'post', $id, 'isg_cta_url' ) ) ) { update_post_meta( $id, 'isg_cta_url', esc_url_raw( $row[6] ) ); }
        }
    }

    $services = array(
        array( 'solflare-registration', 'Solflare crypto card registration', 'Web3 / external services', 'DEMO content from the approved brief. Solflare is a third-party product; ISG’s role, service availability, requirements and fees must be confirmed. This record does not claim that ISG issues the card.', 'web3' ),
        array( 'passive-income-setup', 'Web3 service setup assistance', 'Web3 / configuration', 'DEMO service copy. Describe only approved activities such as reviewing settings, explaining the interface and helping with configuration. Returns are not guaranteed; fees and risks depend on the external service.', 'web3' ),
        array( 'skysend-processing', 'Payment transaction processing', 'SkySend / payment infrastructure', 'DEMO service covering an agreed transaction workflow: operation statuses, participant data exchange, error handling, monitoring and reporting. Providers and service levels must be defined by contract.', 'skysend' ),
        array( 'custom-interfaces', 'Custom user interface development', 'SkySend / interfaces', 'DEMO service for kiosk screens, customer portals or operator interfaces based on approved user workflows and brand guidelines.', 'skysend' ),
        array( 'software-development', 'Software development and support', 'Development', 'DEMO description of work: requirements analysis, component development, API and device integration, testing, documentation and ongoing support.', 'development' ),
        array( 'it-audit', 'IT system design and audit', 'Analytics', 'DEMO analytics service covering architecture and integration review, identification of constraints, recommendations and technical specifications.', 'analytics' ),
        array( 'secure-infrastructure', 'Secure infrastructure implementation', 'Implementation', 'DEMO service for server environments, network rules, access control, logging and support requirements. Scope must be agreed before estimating the work.', 'implementation' ),
    );
    foreach ( $services as $row ) {
        $id = isg_site_seed_post( 'isg_service', $row[0], $row[1], $row[3], $row[2], '', 'DEMO content. Replace with approved service information before publication.' );
        $was_created = $id ? isg_site_last_seed_created() : 0;
        $created += $was_created;
        if ( $id ) {
            $assigned_terms = wp_get_object_terms( $id, 'isg_service_group', array( 'fields' => 'ids' ) );
            if ( $was_created || ( ! is_wp_error( $assigned_terms ) && empty( $assigned_terms ) ) ) { wp_set_object_terms( $id, array( $row[4] ), 'isg_service_group', false ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_subtitle' ) ) { update_post_meta( $id, 'isg_subtitle', $row[2] ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_cta_label' ) ) { update_post_meta( $id, 'isg_cta_label', 'Discuss requirements' ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_cta_url' ) ) { update_post_meta( $id, 'isg_cta_url', '#contact' ); }
        }
    }

    $vacancies = array(
        array( 'php-backend-developer', 'PHP Backend Developer — sample vacancy', 'DEMO record for the careers listing, application flow and SEO template. This is not a confirmed opening. Replace the responsibilities, requirements, work model and salary with HR-approved details.', 'Sample vacancy', '' ),
        array( 'embedded-developer', 'Embedded Software Developer — sample vacancy', 'DEMO content. Example responsibilities include Linux, devices and peripherals. Confirm the technology stack, experience requirements and employment terms with HR.', 'Sample vacancy', '' ),
        array( 'integration-engineer', 'Integration Engineer — sample vacancy', 'DEMO content. Example role involving external APIs, payment providers and devices. Requirements, work model and compensation are not approved.', 'Sample vacancy', '' ),
        array( 'system-analyst', 'Systems Analyst — sample vacancy', 'DEMO content for a role involving system discovery, requirements, integration diagrams and acceptance criteria. This is not a confirmed opening.', 'Sample vacancy', '' ),
    );
    foreach ( $vacancies as $row ) {
        $id = isg_site_seed_post( 'isg_vacancy', $row[0], $row[1], $row[2], 'DEMO record. Vacancy status has not been confirmed.', $row[4], 'Sample vacancy. Do not treat this as an active job offer.' );
        $was_created = $id ? isg_site_last_seed_created() : 0;
        $created += $was_created;
        if ( $id ) {
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_subtitle' ) ) { update_post_meta( $id, 'isg_subtitle', $row[3] ); }
            if ( $was_created || ! metadata_exists( 'post', $id, 'isg_cta_label' ) ) { update_post_meta( $id, 'isg_cta_label', 'View example' ); }
        }
    }

    $cases = array(
        array( 'self-service-terminal-case', 'Case study: self-service workflow automation', 'DEMO structure: initial challenge, component diagram, implemented functions, success criteria and supported systems. Client and metrics are omitted because they have not been verified.', 'DEMO case — no client or metrics' ),
        array( 'payment-integration-case', 'Case study: payment provider integration', 'DEMO API integration story covering transaction statuses, error handling, reconciliation and operational documentation. Obtain client data and measurable outcomes from the project owner before publication.', 'DEMO case — no client or metrics' ),
        array( 'embedded-software-case', 'Case study: embedded device platform', 'DEMO case structure for embedded software: hardware constraints, system components, updates, testing and ongoing support.', 'DEMO case — no client or metrics' ),
        array( 'city-services-case', 'Case study: digital city service', 'DEMO page for a public-service workflow, integrations, security and operations. This is not a claim that a real project has been delivered.', 'DEMO case — no client or metrics' ),
    );
    foreach ( $cases as $row ) {
        $id = isg_site_seed_post( 'isg_partner', $row[0], $row[1], $row[2], $row[3], '', 'DEMO case with no verified client. Replace with approved results and materials before publication.' );
        $was_created = $id ? isg_site_last_seed_created() : 0;
        $created += $was_created;
        if ( $id && ( $was_created || ! metadata_exists( 'post', $id, 'isg_subtitle' ) ) ) { update_post_meta( $id, 'isg_subtitle', $row[3] ); }
    }

    $news = array(
        array( 'demo-news-allvend', 'DEMO · Self-service software platform update', 'Example news about platform development: a change summary, affected device types and update workflow. This is not a real release announcement.', 'ALLVEND' ),
        array( 'demo-news-skysend', 'DEMO · New service provider integration', 'Example news about connecting an external provider, supported workflows, testing requirements and partner information. This post exists only to demonstrate the layout.', 'SkySend' ),
        array( 'demo-news-company', 'DEMO · Company at an industry event', 'Example event coverage with a topic, key discussion points and participant materials. The date, attendance and quotes need confirmation.', 'ISG / News' ),
        array( 'demo-news-fastsys', 'DEMO · Embedded software environment update', 'Example summary of changes to an embedded software environment and supported configurations. It does not describe an actual release.', 'FastSYS' ),
    );
    foreach ( $news as $row ) {
        $id = isg_site_seed_post( 'post', $row[0], $row[1], $row[2], 'DEMO post for reviewing the news card layout.', '', 'DEMO news. Not a factual company announcement.' );
        $was_created = $id ? isg_site_last_seed_created() : 0;
        $created += $was_created;
        if ( $id && ( $was_created || ! metadata_exists( 'post', $id, 'isg_subtitle' ) ) ) { update_post_meta( $id, 'isg_subtitle', $row[3] ); }
    }

    $created += isg_site_seed_pages();
    isg_site_repair_shop_page_assignment();
    if ( class_exists( 'WooCommerce' ) ) { $created += isg_site_seed_products(); }

    // Always ensure both demo-language variants. If Polylang/WPML is active,
    // the records are connected as translations; otherwise the admin page warns
    // that the language plugin must be configured to expose language routes.
    $created += isg_site_seed_multilingual_demo_content();
    isg_site_ensure_primary_menu();
    isg_site_ensure_russian_demo_menu();
    return $created;
}

/** Internal result flag for the last isg_site_seed_post call (1 = inserted, 0 = existing/error). */
function isg_site_last_seed_created(): int {
    $created = (int) ( $GLOBALS['isg_site_last_seed_created'] ?? 0 );
    $GLOBALS['isg_site_last_seed_created'] = 0;
    return $created;
}


/** Return true when Polylang is active and an English language with slug en exists. */
function isg_site_polylang_has_english(): bool {
    if ( ! function_exists( 'pll_languages_list' ) ) { return false; }
    $languages = pll_languages_list( array( 'fields' => 'slug' ) );
    return is_array( $languages ) && in_array( 'en', $languages, true );
}

function isg_site_ensure_term( string $taxonomy, string $slug, string $name ): int {
    $exists = term_exists( $slug, $taxonomy );
    if ( $exists ) { return is_array( $exists ) ? (int) $exists['term_id'] : (int) $exists; }
    $inserted = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
    if ( is_wp_error( $inserted ) ) { return 0; }
    $term_id = (int) $inserted['term_id'];
    if ( function_exists( 'pll_set_term_language' ) && isg_site_polylang_has_english() ) { pll_set_term_language( $term_id, 'en' ); }
    return $term_id;
}

function isg_site_seed_post( string $type, string $slug, string $title, string $content, string $excerpt, string $cta_url, string $demo_note ): int {
    $GLOBALS['isg_site_last_seed_created'] = 0;
    $existing = get_page_by_path( $slug, OBJECT, $type );
    if ( $existing instanceof WP_Post ) { return (int) $existing->ID; }
    $id = wp_insert_post( array(
        'post_type' => $type,
        'post_status' => 'publish',
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => wp_kses_post( wpautop( $content ) ),
        'post_excerpt' => sanitize_textarea_field( $excerpt ),
    ), true );
    if ( is_wp_error( $id ) ) { return 0; }
    if ( function_exists( 'pll_set_post_language' ) && isg_site_polylang_has_english() ) { pll_set_post_language( (int) $id, 'en' ); }
    update_post_meta( $id, 'isg_demo_content', '1' );
    update_post_meta( $id, 'isg_demo_note', sanitize_text_field( $demo_note ) );
    $GLOBALS['isg_site_last_seed_created'] = 1;
    if ( $cta_url ) { update_post_meta( $id, 'isg_cta_url', esc_url_raw( $cta_url ) ); }
    return (int) $id;
}

function isg_site_seed_pages(): int {
    $pages = array(
        'home' => array( 'Home', '<p>DEMO homepage record. The visual layout is rendered by the active theme template, front-page.php.</p>' ),
        'news' => array( 'News', '<p>DEMO news page. Posts are displayed through the standard WordPress loop.</p>' ),
        'company' => array( 'Company', '<p>Company overview placeholder for mission, history, development approach and service areas. Replace it with approved corporate information.</p>' ),
        'contacts' => array( 'Contact', '<p>DEMO page. Customer contact details and a working contact form are not connected.</p>' ),
        'privacy-policy' => array( 'Privacy policy', '<p>PLACEHOLDER. Add the approved privacy and personal data processing policy before publishing.</p>' ),
        'sitemap' => array( 'Sitemap', '<!-- wp:shortcode -->[isg_sitemap]<!-- /wp:shortcode -->' ),
        'support' => array( 'Support', '<p>Downloads, feedback, instructions and support materials belong here. Files and contact workflows must be approved by the customer.</p>' ),
        'shop' => array( 'Shop', '<p>Product catalogue. Activate WooCommerce to enable the shop functionality.</p>' ),
    );
    $created = 0;
    foreach ( $pages as $slug => $data ) {
        $existing = get_page_by_path( $slug, OBJECT, 'page' );
        if ( $existing instanceof WP_Post ) { $id = (int) $existing->ID; }
        else {
            $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $data[0], 'post_name' => $slug, 'post_content' => $data[1], 'post_excerpt' => 'DEMO page. Replace this content with approved copy.' ), true );
            if ( is_wp_error( $id ) ) { continue; }
            if ( function_exists( 'pll_set_post_language' ) && isg_site_polylang_has_english() ) { pll_set_post_language( (int) $id, 'en' ); }
            update_post_meta( $id, 'isg_demo_content', '1' );
            update_post_meta( $id, 'isg_demo_note', 'DEMO page. Review and replace before launch.' );
            ++$created;
        }
        if ( 'shop' === $slug ) { update_post_meta( $id, '_isg_demo_shop_page', '1' ); }
        if ( 'home' === $slug && ! (int) get_option( 'page_on_front' ) ) { update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $id ); }
        if ( 'news' === $slug && ! (int) get_option( 'page_for_posts' ) ) { update_option( 'page_for_posts', $id ); }
    }
    if ( class_exists( 'WooCommerce' ) ) { isg_site_repair_shop_page_assignment(); }
    return $created;
}

/** Repair a stale WooCommerce shop-page ID that points at a product or unrelated page. */
function isg_site_repair_shop_page_assignment(): void {
    if ( ! class_exists( 'WooCommerce' ) ) { return; }

    // Use an unfiltered lookup so the active language cannot hide the English source shop page.
    $matches = get_posts( array(
        'post_type' => 'page',
        'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
        'name' => 'shop',
        'posts_per_page' => 1,
        'suppress_filters' => true,
        'orderby' => 'ID',
        'order' => 'ASC',
    ) );
    $shop = ! empty( $matches ) && $matches[0] instanceof WP_Post ? $matches[0] : null;
    if ( ! $shop ) { return; }

    update_post_meta( $shop->ID, '_isg_demo_shop_page', '1' );
    if ( function_exists( 'pll_get_post' ) ) {
        $languages = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
        foreach ( (array) $languages as $language ) {
            $translated_id = (int) pll_get_post( (int) $shop->ID, (string) $language );
            $translated_page = $translated_id ? get_post( $translated_id ) : null;
            if ( $translated_page instanceof WP_Post && 'page' === $translated_page->post_type ) {
                update_post_meta( $translated_id, '_isg_demo_shop_page', '1' );
            }
        }
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'code' ) );
        foreach ( (array) $languages as $language ) {
            $code = is_array( $language ) ? (string) ( $language['language_code'] ?? '' ) : '';
            if ( '' === $code ) { continue; }
            $translated_id = (int) apply_filters( 'wpml_object_id', (int) $shop->ID, 'page', false, $code );
            $translated_page = $translated_id ? get_post( $translated_id ) : null;
            if ( $translated_page instanceof WP_Post && 'page' === $translated_page->post_type ) {
                update_post_meta( $translated_id, '_isg_demo_shop_page', '1' );
            }
        }
    }

    // Repair only the exact legacy demo copy that incorrectly claims WooCommerce is inactive.
    // Never overwrite content an editor has changed.
    isg_site_repair_ru_shop_placeholder( (int) $shop->ID );

    // Keep valid paired pages intact. Only reset the option when it points outside this shop translation group.
    $known_shop_ids = array( (int) $shop->ID );
    if ( function_exists( 'pll_get_post' ) ) {
        $languages = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
        foreach ( (array) $languages as $language ) {
            $translated_id = (int) pll_get_post( (int) $shop->ID, (string) $language );
            if ( $translated_id ) { $known_shop_ids[] = $translated_id; }
        }
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'code' ) );
        foreach ( (array) $languages as $language ) {
            $code = is_array( $language ) ? (string) ( $language['language_code'] ?? '' ) : '';
            if ( '' !== $code ) {
                $translated_id = (int) apply_filters( 'wpml_object_id', (int) $shop->ID, 'page', false, $code );
                if ( $translated_id ) { $known_shop_ids[] = $translated_id; }
            }
        }
    }
    $configured_id = absint( get_option( 'woocommerce_shop_page_id', 0 ) );
    if ( ! in_array( $configured_id, array_unique( $known_shop_ids ), true ) ) {
        update_option( 'woocommerce_shop_page_id', (int) $shop->ID );
    }
}


/** Update the exact old generated Russian shop placeholder without touching editorial content. */
function isg_site_repair_ru_shop_placeholder( int $english_shop_id ): void {
    $russian_id = 0;
    if ( function_exists( 'pll_get_post' ) ) {
        $russian_id = (int) pll_get_post( $english_shop_id, 'ru' );
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $russian_id = (int) apply_filters( 'wpml_object_id', $english_shop_id, 'page', false, 'ru' );
    }

    // The seeder's conventional slug also allows a safe repair if a translation link was not saved.
    if ( ! $russian_id ) {
        $by_slug = get_page_by_path( 'shop-ru', OBJECT, 'page' );
        if ( $by_slug instanceof WP_Post ) { $russian_id = (int) $by_slug->ID; }
    }
    if ( ! $russian_id || '1' !== get_post_meta( $russian_id, 'isg_demo_content', true ) ) { return; }

    $page = get_post( $russian_id );
    if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) { return; }

    $current = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $page->post_content ) ) );
    $legacy_placeholder = 'Каталог товаров. Для включения функциональности магазина активируйте WooCommerce.';
    if ( $legacy_placeholder !== $current ) { return; }

    wp_update_post( array(
        'ID' => $russian_id,
        'post_content' => 'Каталог товаров. Для вывода магазина на русском языке требуется настроить интеграцию WooCommerce с используемым мультиязычным плагином. Для Polylang нужен отдельный модуль Polylang for WooCommerce; для WPML — WPML Multilingual & Multicurrency for WooCommerce. Эти модули не входят в тестовый архив.',
    ) );
}

function isg_site_seed_products(): int {
    if ( ! class_exists( 'WC_Product_Simple' ) || ! function_exists( 'wc_get_product' ) ) { return 0; }
    $categories = array(
        'software' => isg_site_ensure_term( 'product_cat', 'software', 'Software' ),
        'services' => isg_site_ensure_term( 'product_cat', 'services', 'Services' ),
        'consulting' => isg_site_ensure_term( 'product_cat', 'consulting', 'Consulting and setup' ),
        'devices' => isg_site_ensure_term( 'product_cat', 'devices', 'Devices and components' ),
    );
    $products = array(
        array( 'solflare-card', 'Solflare Card registration — sample service', 'services', 'DEMO product from the brief. Solflare is a third-party product. ISG’s role, terms and price have not been confirmed; orders stay unavailable until a price and an approved payment method are configured.' ),
        array( 'passive-income-setup', 'Web3 service setup — sample service', 'consulting', 'DEMO catalogue item. Returns are not guaranteed. Price, scope of work and supported services require approval.' ),
        array( 'allvend', 'ALLVEND software — request terms', 'software', 'DEMO product record. Confirm the current configuration, licensing, price, device requirements and scope of delivery before publication.' ),
        array( 'skysend', 'SkySend — request terms', 'software', 'DEMO system record. Pricing, functional configuration, connected providers and implementation requirements are not included in this test content.' ),
        array( 'fastsys', 'FastSYS — request specifications', 'software', 'DEMO embedded software record. Confirm version, supported hardware configurations and licensing terms.' ),
        array( 'software-audit', 'IT systems audit — sample service', 'services', 'DEMO service. Scope and price should be based on the project size and approved before publication.' ),
        array( 'device-integration', 'Device integration — sample service', 'services', 'DEMO integration service for software and peripheral hardware. Compatibility depends on the exact device model and interfaces.' ),
        array( 'technical-consultation', 'Technical consultation — sample service', 'consulting', 'DEMO catalogue item. This is not a commercial offer; price and duration must be agreed.' ),
    );
    $created = 0;
    foreach ( $products as $row ) {
        if ( get_page_by_path( $row[0], OBJECT, 'product' ) ) { continue; }
        try {
            $product = new WC_Product_Simple();
            $product->set_name( $row[1] );
            $product->set_slug( $row[0] );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'visible' );
            $product->set_description( wp_kses_post( wpautop( $row[3] ) ) );
            $product->set_short_description( 'DEMO. Price and terms are not set; checkout is disabled.' );
            $product->set_virtual( true );
            $product->set_downloadable( false );
            $product->set_regular_price( '' );
            $product->set_price( '' );
            $product->set_stock_status( 'instock' );
            $product->set_manage_stock( false );
            $id = $product->save();
        } catch ( Throwable $error ) {
            continue;
        }
        if ( ! $id ) { continue; }
        if ( ! empty( $categories[ $row[2] ] ) ) { wp_set_object_terms( $id, array( $categories[ $row[2] ] ), 'product_cat', false ); }
        if ( function_exists( 'pll_set_post_language' ) && isg_site_polylang_has_english() ) { pll_set_post_language( (int) $id, 'en' ); }
        update_post_meta( $id, 'isg_demo_content', '1' );
        update_post_meta( $id, 'isg_demo_note', 'DEMO product without a price. Do not accept orders until commercial terms and payment setup are approved.' );
        ++$created;
    }
    return $created;
}

function isg_site_ensure_primary_menu(): void {
    $locations = (array) get_theme_mod( 'nav_menu_locations', array() );
    $assigned_id = absint( $locations['primary'] ?? 0 );
    $assigned_menu = $assigned_id ? wp_get_nav_menu_object( $assigned_id ) : false;
    $assigned_items = $assigned_menu ? (array) wp_get_nav_menu_items( $assigned_id ) : array();
    $assigned_language = '';
    if ( $assigned_menu && function_exists( 'pll_get_term_language' ) ) {
        $assigned_language = (string) pll_get_term_language( $assigned_id, 'slug' );
    }

    // Keep an existing, populated non-Russian menu. Repair a missing, empty or RU-only
    // primary assignment by selecting the English demo menu so Polylang can switch to its translation.
    $needs_repair = ! $assigned_menu || empty( $assigned_items ) || 'ru' === $assigned_language;
    if ( $needs_repair ) {
        $menu = wp_get_nav_menu_object( 'ISG Primary Menu (Demo)' );
        $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( 'ISG Primary Menu (Demo)' );
        if ( is_wp_error( $menu_id ) || ! $menu_id ) { return; }
        $locations['primary'] = (int) $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    } else {
        $menu_id = (int) $assigned_menu->term_id;
    }

    if ( function_exists( 'pll_set_term_language' ) && isg_site_polylang_has_english() ) {
        $menu_language = function_exists( 'pll_get_term_language' ) ? (string) pll_get_term_language( (int) $menu_id, 'slug' ) : '';
        if ( ( $needs_repair && 'en' !== $menu_language ) || '' === $menu_language ) {
            pll_set_term_language( (int) $menu_id, 'en' );
        }
    }

    $products_url = get_post_type_archive_link( 'isg_solution' );
    $services_url = get_post_type_archive_link( 'isg_service' );
    $items = array(
        array( 'name' => 'Products', 'url' => $products_url ?: home_url( '/products/' ), 'parent' => 0 ),
        array( 'name' => 'Services', 'url' => $services_url ?: home_url( '/services/' ), 'parent' => 0 ),
        array( 'name' => 'Industries', 'url' => ( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) ) . '#industries', 'parent' => 0 ),
        array( 'name' => 'Company', 'url' => function_exists( 'isg_theme_localized_page_url' ) ? isg_theme_localized_page_url( 'company', '/#company' ) : home_url( '/#company' ), 'parent' => 0 ),
    );
    $existing_items = (array) wp_get_nav_menu_items( $menu_id );
    $parent_ids = array();
    foreach ( $items as $item ) {
        $found = null;
        foreach ( $existing_items as $existing_item ) {
            if ( $existing_item->title === $item['name'] && 0 === (int) $existing_item->menu_item_parent ) { $found = $existing_item; break; }
        }
        if ( $found ) { $parent_ids[ $item['name'] ] = (int) $found->ID; continue; }
        $item_id = wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title' => $item['name'], 'menu-item-type' => 'custom',
            'menu-item-url' => $item['url'], 'menu-item-status' => 'publish',
        ) );
        if ( ! is_wp_error( $item_id ) ) { $parent_ids[ $item['name'] ] = (int) $item_id; }
    }

    $children = array(
        'Products' => array(
            array( 'ALLVEND', 'allvend', 'isg_solution' ),
            array( 'SkySend', 'skysend', 'isg_solution' ),
            array( 'FastSYS', 'fastsys', 'isg_solution' ),
            array( 'FINGER', 'finger', 'isg_solution' ),
            array( 'Beauty kiosks', 'beauty-kiosk', 'isg_solution' ),
            array( 'Smart City', 'smart-city', 'isg_solution' ),
        ),
        'Services' => array(
            array( 'Solflare Card registration', 'solflare-registration', 'isg_service' ),
            array( 'Web3 service setup', 'passive-income-setup', 'isg_service' ),
            array( 'SkySend services', 'skysend-processing', 'isg_service' ),
            array( 'Software development', 'software-development', 'isg_service' ),
            array( 'IT systems audit', 'it-audit', 'isg_service' ),
            array( 'Infrastructure implementation', 'secure-infrastructure', 'isg_service' ),
        ),
    );
    foreach ( $children as $parent_name => $child_items ) {
        if ( empty( $parent_ids[ $parent_name ] ) ) { continue; }
        foreach ( $child_items as $child ) {
            $child_post = get_page_by_path( $child[1], OBJECT, $child[2] );
            $url = $child_post instanceof WP_Post ? get_permalink( $child_post ) : ( 'isg_solution' === $child[2] ? ( $products_url ?: home_url( '/products/' ) ) : ( $services_url ?: home_url( '/services/' ) ) );
            $found = false;
            foreach ( $existing_items as $existing_item ) {
                if ( $existing_item->title === $child[0] && (int) $existing_item->menu_item_parent === $parent_ids[ $parent_name ] ) { $found = true; break; }
            }
            if ( $found ) { continue; }
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title' => $child[0], 'menu-item-type' => 'custom',
                'menu-item-url' => $url, 'menu-item-parent-id' => $parent_ids[ $parent_name ], 'menu-item-status' => 'publish',
            ) );
        }
    }
}

/** Languages are considered configured only when both en and ru are available. */
function isg_site_demo_languages_configured(): bool {
    if ( function_exists( 'pll_languages_list' ) ) {
        $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
        return is_array( $slugs ) && in_array( 'en', $slugs, true ) && in_array( 'ru', $slugs, true );
    }
    return isg_site_wpml_has_both_languages();
}

/** Assign an existing source/target pair to languages and connect their translation IDs. */
function isg_site_wpml_has_both_languages(): bool {
    if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) { return false; }
    $languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'code' ) );
    if ( ! is_array( $languages ) ) { return false; }
    $codes = array();
    foreach ( $languages as $key => $language ) {
        if ( is_array( $language ) && ! empty( $language['language_code'] ) ) { $codes[] = (string) $language['language_code']; }
        else { $codes[] = (string) $key; }
    }
    return in_array( 'en', $codes, true ) && in_array( 'ru', $codes, true );
}

function isg_site_wpml_link_element_pair( string $element_type, int $english_id, int $russian_id ): void {
    if ( ! isg_site_wpml_has_both_languages() || ! $english_id || ! $russian_id ) { return; }
    $trid = apply_filters( 'wpml_element_trid', null, $english_id, $element_type );
    if ( ! $trid ) {
        do_action( 'wpml_set_element_language_details', array(
            'element_id' => $english_id, 'element_type' => $element_type,
            'trid' => false, 'language_code' => 'en', 'source_language_code' => null,
        ) );
        $trid = apply_filters( 'wpml_element_trid', null, $english_id, $element_type );
    }
    if ( $trid ) {
        do_action( 'wpml_set_element_language_details', array(
            'element_id' => $russian_id, 'element_type' => $element_type,
            'trid' => $trid, 'language_code' => 'ru', 'source_language_code' => 'en',
        ) );
    }
}

/** Assign an existing source/target pair to languages and connect their translation IDs. */
function isg_site_link_demo_post_translations( string $post_type, int $english_id, int $russian_id ): void {
    if ( ! $english_id || ! $russian_id ) { return; }
    if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {
        $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
        if ( is_array( $slugs ) && in_array( 'en', $slugs, true ) && in_array( 'ru', $slugs, true ) ) {
            pll_set_post_language( $english_id, 'en' );
            pll_set_post_language( $russian_id, 'ru' );
            pll_save_post_translations( array( 'en' => $english_id, 'ru' => $russian_id ) );
        }
        return;
    }
    if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        isg_site_wpml_link_element_pair( (string) apply_filters( 'wpml_element_type', $post_type ), $english_id, $russian_id );
    }
}

function isg_site_link_demo_term_translations( string $taxonomy, int $english_id, int $russian_id ): void {
    if ( ! $english_id || ! $russian_id ) { return; }
    if ( function_exists( 'pll_set_term_language' ) && function_exists( 'pll_save_term_translations' ) ) {
        $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
        if ( is_array( $slugs ) && in_array( 'en', $slugs, true ) && in_array( 'ru', $slugs, true ) ) {
            pll_set_term_language( $english_id, 'en' );
            pll_set_term_language( $russian_id, 'ru' );
            pll_save_term_translations( array( 'en' => $english_id, 'ru' => $russian_id ) );
        }
        return;
    }
    if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        isg_site_wpml_link_element_pair( (string) apply_filters( 'wpml_element_type', 'tax_' . $taxonomy ), $english_id, $russian_id );
    }
}

/** Find a translated term using the language plugin or the conventional -ru slug. */
function isg_site_demo_ru_term_id( int $term_id, string $taxonomy ): int {
    if ( function_exists( 'pll_get_term' ) ) {
        $translated = (int) pll_get_term( $term_id, 'ru' );
        if ( $translated ) { return $translated; }
    }
    if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $translated = (int) apply_filters( 'wpml_object_id', $term_id, $taxonomy, false, 'ru' );
        if ( $translated ) { return $translated; }
    }
    $term = get_term( $term_id, $taxonomy );
    if ( $term && ! is_wp_error( $term ) ) {
        $by_slug = get_term_by( 'slug', $term->slug . '-ru', $taxonomy );
        if ( $by_slug && ! is_wp_error( $by_slug ) ) { return (int) $by_slug->term_id; }
    }
    return 0;
}

function isg_site_ensure_ru_demo_term( string $taxonomy, string $english_slug, string $russian_name ): void {
    $english_term = get_term_by( 'slug', $english_slug, $taxonomy );
    if ( ! $english_term || is_wp_error( $english_term ) ) { return; }
    $russian_slug = $english_slug . '-ru';
    $russian_term_id = isg_site_ensure_term( $taxonomy, $russian_slug, $russian_name );
    if ( ! $russian_term_id ) { return; }
    isg_site_link_demo_term_translations( $taxonomy, (int) $english_term->term_id, $russian_term_id );
}

/** Translate all taxonomy assignments from the English demo record onto its Russian pair. */
function isg_site_sync_demo_translation_terms( string $post_type, int $english_id, int $russian_id ): void {
    foreach ( get_object_taxonomies( $post_type, 'names' ) as $taxonomy ) {
        $current_russian_terms = wp_get_object_terms( $russian_id, $taxonomy, array( 'fields' => 'ids' ) );
        if ( ! is_wp_error( $current_russian_terms ) && ! empty( $current_russian_terms ) ) { continue; }
        $english_terms = wp_get_object_terms( $english_id, $taxonomy, array( 'fields' => 'ids' ) );
        if ( is_wp_error( $english_terms ) || ! $english_terms ) { continue; }
        $russian_terms = array();
        foreach ( $english_terms as $term_id ) {
            $translated_id = isg_site_demo_ru_term_id( (int) $term_id, $taxonomy );
            if ( $translated_id ) { $russian_terms[] = $translated_id; }
        }
        if ( $russian_terms ) { wp_set_object_terms( $russian_id, $russian_terms, $taxonomy, false ); }
    }
}

/** Create one missing Russian record for an existing English demo post without overwriting either side. */
function isg_site_ensure_ru_demo_post( string $post_type, string $english_slug, array $translation ): int {
    $english = get_page_by_path( $english_slug, OBJECT, $post_type );
    if ( ! $english instanceof WP_Post || '1' !== get_post_meta( $english->ID, 'isg_demo_content', true ) ) { return 0; }
    $english_id = (int) $english->ID;
    $russian_slug = $english_slug . '-ru';
    $russian = get_page_by_path( $russian_slug, OBJECT, $post_type );
    if ( $russian instanceof WP_Post && '1' !== get_post_meta( $russian->ID, 'isg_demo_content', true ) ) {
        // Do not attach or alter a manually authored page that happens to use this slug.
        return 0;
    }
    $created = 0;
    $newly_created = false;
    if ( ! $russian instanceof WP_Post ) {
        $content = (string) ( $translation['content'] ?? '' );
        $inserted = wp_insert_post( array(
            'post_type' => $post_type,
            'post_status' => $english->post_status,
            'post_title' => sanitize_text_field( (string) $translation['title'] ),
            'post_name' => $russian_slug,
            'post_content' => wp_kses_post( wpautop( $content ) ),
            'post_excerpt' => sanitize_textarea_field( (string) ( $translation['excerpt'] ?? '' ) ),
            'post_author' => (int) $english->post_author,
            'menu_order' => (int) $english->menu_order,
        ), true );
        if ( is_wp_error( $inserted ) ) { return 0; }
        $russian_id = (int) $inserted;
        update_post_meta( $russian_id, 'isg_demo_content', '1' );
        ++$created;
        $newly_created = true;
    } else {
        $russian_id = (int) $russian->ID;
    }
    if ( 'page' === $post_type && 'shop' === $english_slug ) {
        update_post_meta( $english_id, '_isg_demo_shop_page', '1' );
        update_post_meta( $russian_id, '_isg_demo_shop_page', '1' );
    }

    // Copy only language-neutral demo markers plus explicit translated fields.
    if ( $newly_created || ! metadata_exists( 'post', $russian_id, 'isg_demo_note' ) ) {
        update_post_meta( $russian_id, 'isg_demo_note', sanitize_text_field( (string) ( $translation['demo_note'] ?? 'ДЕМО-материал. Проверьте и замените перед запуском.' ) ) );
    }
    $common_meta = array( 'isg_cta_url', 'isg_demo_url', 'isg_case_year' );
    foreach ( $common_meta as $meta_key ) {
        if ( metadata_exists( 'post', $english_id, $meta_key ) && ! metadata_exists( 'post', $russian_id, $meta_key ) ) {
            update_post_meta( $russian_id, $meta_key, get_post_meta( $english_id, $meta_key, true ) );
        }
    }
    foreach ( (array) ( $translation['meta'] ?? array() ) as $meta_key => $value ) {
        if ( is_string( $value ) && str_starts_with( $meta_key, 'isg_' ) && ( $newly_created || ! metadata_exists( 'post', $russian_id, $meta_key ) ) ) {
            update_post_meta( $russian_id, $meta_key, $value );
        }
    }
    isg_site_sync_demo_translation_terms( $post_type, $english_id, $russian_id );
    isg_site_link_demo_post_translations( $post_type, $english_id, $russian_id );
    return $created;
}

/** Create the Russian WooCommerce equivalent while keeping both products unpriced. */
function isg_site_ensure_ru_demo_product( string $english_slug, array $translation ): int {
    if ( ! class_exists( 'WC_Product_Simple' ) || ! function_exists( 'wc_get_product' ) ) { return 0; }
    $english = get_page_by_path( $english_slug, OBJECT, 'product' );
    if ( ! $english instanceof WP_Post || '1' !== get_post_meta( $english->ID, 'isg_demo_content', true ) ) { return 0; }
    $english_id = (int) $english->ID;
    $russian_slug = $english_slug . '-ru';
    $existing = get_page_by_path( $russian_slug, OBJECT, 'product' );
    $created = 0;
    $newly_created = false;
    if ( $existing instanceof WP_Post && '1' !== get_post_meta( $existing->ID, 'isg_demo_content', true ) ) { return 0; }
    if ( ! $existing instanceof WP_Post ) {
        try {
            $product = new WC_Product_Simple();
            $product->set_name( (string) $translation['title'] );
            $product->set_slug( $russian_slug );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'visible' );
            $product->set_description( wp_kses_post( wpautop( (string) $translation['content'] ) ) );
            $product->set_short_description( sanitize_textarea_field( (string) $translation['excerpt'] ) );
            $product->set_virtual( true );
            $product->set_downloadable( false );
            $product->set_regular_price( '' );
            $product->set_price( '' );
            $product->set_stock_status( 'instock' );
            $product->set_manage_stock( false );
            $id = (int) $product->save();
        } catch ( Throwable $error ) { return 0; }
        if ( ! $id ) { return 0; }
        $russian_id = $id;
        update_post_meta( $russian_id, 'isg_demo_content', '1' );
        ++$created;
        $newly_created = true;
    } else {
        $russian_id = (int) $existing->ID;
    }
    if ( $newly_created || ! metadata_exists( 'post', $russian_id, 'isg_demo_note' ) ) {
        update_post_meta( $russian_id, 'isg_demo_note', sanitize_text_field( (string) ( $translation['demo_note'] ?? 'ДЕМО-товар без цены. Не принимать заказы.' ) ) );
    }
    isg_site_sync_demo_translation_terms( 'product', $english_id, $russian_id );
    isg_site_link_demo_post_translations( 'product', $english_id, $russian_id );
    return $created;
}

/** Ensure Russian equivalents for each seeded demo item. Existing content is never overwritten. */
function isg_site_seed_multilingual_demo_content(): int {
    $term_translations = array(
        'isg_solution_group' => array(
            'software' => 'Программные продукты', 'finance' => 'Финансовые системы',
            'devices' => 'Устройства и самообслуживание', 'infrastructure' => 'Инфраструктура',
        ),
        'isg_service_group' => array(
            'web3' => 'Услуги Web3', 'skysend' => 'Услуги SkySend', 'development' => 'Разработка ПО',
            'analytics' => 'Аналитика', 'implementation' => 'Внедрение',
        ),
        'product_cat' => array(
            'software' => 'Программное обеспечение', 'services' => 'Услуги',
            'consulting' => 'Консультации и настройка', 'devices' => 'Устройства и компоненты',
        ),
    );
    foreach ( $term_translations as $taxonomy => $terms ) {
        foreach ( $terms as $slug => $russian_name ) { isg_site_ensure_ru_demo_term( $taxonomy, $slug, $russian_name ); }
    }

    $content = array(
        'isg_solution' => array(
            'allvend' => array( 'title' => 'ALLVEND', 'excerpt' => 'Платформа программного обеспечения для киосков и устройств самообслуживания.', 'content' => 'ДЕМО-контент. ALLVEND объединяет пользовательские сценарии для киосков и автоматизированных устройств. Возможности зависят от конфигурации, периферийного оборудования и подключённых поставщиков. Запись предназначена для проверки управления контентом в WordPress.', 'meta' => array( 'isg_subtitle' => 'Платформа ПО для устройств самообслуживания', 'isg_cta_label' => 'Подробнее об ALLVEND' ) ),
            'skysend' => array( 'title' => 'SkySend', 'excerpt' => 'Кластерная система обработки платежей.', 'content' => 'ДЕМО-контент. Пример описания SkySend: взаимодействие подключённых точек, обработка транзакций, мониторинг, балансы и отчётность. Конкретные функции и параметры необходимо подтвердить для фактической поставки.', 'meta' => array( 'isg_subtitle' => 'Кластерная система обработки платежей', 'isg_cta_label' => 'Подробнее о SkySend' ) ),
            'fastsys' => array( 'title' => 'FastSYS', 'excerpt' => 'Встраиваемая операционная система на базе Linux.', 'content' => 'ДЕМО-контент о FastSYS для устройств с ограниченными ресурсами. Перед публикацией подтвердите поддерживаемые конфигурации, модель поставки и характеристики соответствующей версии.', 'meta' => array( 'isg_subtitle' => 'Встраиваемая ОС на базе Linux', 'isg_cta_label' => 'Подробнее о FastSYS' ) ),
            'finger' => array( 'title' => 'FINGER', 'excerpt' => 'Биометрические компоненты и сценарии идентификации.', 'content' => 'ДЕМО-описание направления FINGER. После согласования с владельцем продукта добавьте назначение, поддерживаемые устройства, архитектуру, требования к данным и ограничения использования.', 'meta' => array( 'isg_subtitle' => 'Биометрические компоненты и идентификация', 'isg_cta_label' => 'Запросить информацию' ) ),
            'beauty-kiosk' => array( 'title' => 'Beauty kiosks', 'excerpt' => 'Интерактивные устройства и киоски самообслуживания.', 'content' => 'ДЕМО-карточка оборудования. Добавьте согласованные изображения, доступные варианты, интерфейсы и технические характеристики из актуальной спецификации.', 'meta' => array( 'isg_subtitle' => 'Интерактивные устройства и киоски', 'isg_cta_label' => 'Запросить спецификацию' ) ),
            'smart-city' => array( 'title' => 'Умный город', 'excerpt' => 'Интегрированные решения для цифровой городской инфраструктуры.', 'content' => 'ДЕМО-описание решений для интеграции систем, автоматизации процессов и взаимодействия с городскими сервисами. Объём конкретного проекта определяется после обследования.', 'meta' => array( 'isg_subtitle' => 'Решения для цифровой городской инфраструктуры', 'isg_cta_label' => 'Обсудить проект' ) ),
        ),
        'isg_service' => array(
            'solflare-registration' => array( 'title' => 'Помощь с регистрацией криптокарты Solflare', 'excerpt' => 'Web3 / сторонние сервисы', 'content' => 'ДЕМО-контент из утверждённого задания. Solflare — сторонний продукт; роль ISG, доступность услуги, требования и стоимость должны быть подтверждены. Эта запись не утверждает, что ISG выпускает карту.', 'meta' => array( 'isg_subtitle' => 'Web3 / сторонние сервисы', 'isg_cta_label' => 'Обсудить условия', 'isg_cta_url' => '#contact' ) ),
            'passive-income-setup' => array( 'title' => 'Помощь с настройкой Web3-сервисов', 'excerpt' => 'Web3 / настройка', 'content' => 'ДЕМО-описание услуги. Допускается описывать только согласованные действия: проверку настроек, пояснение интерфейса и помощь с конфигурацией. Доходность не гарантируется; комиссии и риски зависят от внешнего сервиса.', 'meta' => array( 'isg_subtitle' => 'Web3 / настройка', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
            'skysend-processing' => array( 'title' => 'Обработка платёжных транзакций', 'excerpt' => 'SkySend / платёжная инфраструктура', 'content' => 'ДЕМО-услуга по согласованному процессу обработки транзакций: статусы операций, обмен данными участников, обработка ошибок, мониторинг и отчётность. Провайдеры и уровни сервиса определяются договором.', 'meta' => array( 'isg_subtitle' => 'SkySend / платёжная инфраструктура', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
            'custom-interfaces' => array( 'title' => 'Разработка пользовательских интерфейсов', 'excerpt' => 'SkySend / интерфейсы', 'content' => 'ДЕМО-услуга по разработке экранов киосков, клиентских порталов и интерфейсов операторов на основе согласованных сценариев и брендбука.', 'meta' => array( 'isg_subtitle' => 'SkySend / интерфейсы', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
            'software-development' => array( 'title' => 'Разработка и поддержка ПО', 'excerpt' => 'Разработка программного обеспечения', 'content' => 'ДЕМО-описание работ: анализ требований, разработка компонентов, интеграция API и устройств, тестирование, документация и последующая поддержка.', 'meta' => array( 'isg_subtitle' => 'Разработка программного обеспечения', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
            'it-audit' => array( 'title' => 'Проектирование и аудит IT-систем', 'excerpt' => 'Аналитика', 'content' => 'ДЕМО-услуга по анализу архитектуры и интеграций, выявлению ограничений, подготовке рекомендаций и технических заданий.', 'meta' => array( 'isg_subtitle' => 'Аналитика', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
            'secure-infrastructure' => array( 'title' => 'Внедрение защищённой инфраструктуры', 'excerpt' => 'Внедрение', 'content' => 'ДЕМО-услуга для серверной инфраструктуры, сетевых правил, управления доступом, журналирования и поддержки. Объём работ согласуется до подготовки сметы.', 'meta' => array( 'isg_subtitle' => 'Внедрение', 'isg_cta_label' => 'Обсудить требования', 'isg_cta_url' => '#contact' ) ),
        ),
        'isg_vacancy' => array(
            'php-backend-developer' => array( 'title' => 'PHP Backend-разработчик — пример вакансии', 'excerpt' => 'ДЕМО-карточка. Активность вакансии не подтверждена.', 'content' => 'ДЕМО-запись для проверки страницы карьеры, формы отклика и SEO-шаблона. Это не подтверждённая вакансия. Обязанности, требования, формат работы и зарплату необходимо заменить данными, согласованными с HR.', 'meta' => array( 'isg_subtitle' => 'Пример вакансии', 'isg_cta_label' => 'Посмотреть пример' ) ),
            'embedded-developer' => array( 'title' => 'Разработчик встраиваемого ПО — пример вакансии', 'excerpt' => 'ДЕМО-карточка. Условия не согласованы.', 'content' => 'ДЕМО-контент. Пример задач связан с Linux, устройствами и периферией. Подтвердите технологический стек, требования к опыту и условия трудоустройства у HR.', 'meta' => array( 'isg_subtitle' => 'Пример вакансии', 'isg_cta_label' => 'Посмотреть пример' ) ),
            'integration-engineer' => array( 'title' => 'Инженер по интеграциям — пример вакансии', 'excerpt' => 'ДЕМО-карточка. Условия не согласованы.', 'content' => 'ДЕМО-контент. Пример роли включает интеграцию внешних API, платёжных провайдеров и устройств. Требования, формат работы и компенсация не утверждены.', 'meta' => array( 'isg_subtitle' => 'Пример вакансии', 'isg_cta_label' => 'Посмотреть пример' ) ),
            'system-analyst' => array( 'title' => 'Системный аналитик — пример вакансии', 'excerpt' => 'ДЕМО-карточка. Условия не согласованы.', 'content' => 'ДЕМО-контент для роли с обследованием систем, сбором требований, схемами интеграций и критериями приёмки. Это не подтверждённая активная вакансия.', 'meta' => array( 'isg_subtitle' => 'Пример вакансии', 'isg_cta_label' => 'Посмотреть пример' ) ),
        ),
        'isg_partner' => array(
            'self-service-terminal-case' => array( 'title' => 'Кейс: автоматизация самообслуживания', 'excerpt' => 'ДЕМО-кейс — клиент и метрики не подтверждены.', 'content' => 'ДЕМО-структура кейса: исходная задача, схема компонентов, реализованные функции, критерии успеха и поддерживаемые системы. Клиент и метрики не указаны, так как они не подтверждены.', 'meta' => array( 'isg_subtitle' => 'ДЕМО-кейс — без подтверждённого клиента и метрик', 'isg_case_client' => 'Демо-кейс' ) ),
            'payment-integration-case' => array( 'title' => 'Кейс: интеграция платёжного провайдера', 'excerpt' => 'ДЕМО-кейс — клиент и метрики не подтверждены.', 'content' => 'ДЕМО-история интеграции API: статусы транзакций, обработка ошибок, сверка операций и эксплуатационная документация. Данные клиента и измеримые результаты необходимо получить у владельца проекта до публикации.', 'meta' => array( 'isg_subtitle' => 'ДЕМО-кейс — без подтверждённого клиента и метрик', 'isg_case_client' => 'Демо-кейс' ) ),
            'embedded-software-case' => array( 'title' => 'Кейс: платформа для встраиваемых устройств', 'excerpt' => 'ДЕМО-кейс — клиент и метрики не подтверждены.', 'content' => 'ДЕМО-структура кейса о встраиваемом ПО: ограничения оборудования, компоненты системы, обновления, тестирование и дальнейшая поддержка.', 'meta' => array( 'isg_subtitle' => 'ДЕМО-кейс — без подтверждённого клиента и метрик', 'isg_case_client' => 'Демо-кейс' ) ),
            'city-services-case' => array( 'title' => 'Кейс: цифровой городской сервис', 'excerpt' => 'ДЕМО-страница о процессе предоставления городских услуг, интеграциях, безопасности и эксплуатации. Это не утверждение о выполненном реальном проекте.', 'content' => 'ДЕМО-страница о процессе предоставления городских услуг, интеграциях, безопасности и эксплуатации. Это не утверждение о выполненном реальном проекте.', 'meta' => array( 'isg_subtitle' => 'ДЕМО-кейс — без подтверждённого клиента и метрик', 'isg_case_client' => 'Демо-кейс' ) ),
        ),
        'post' => array(
            'demo-news-allvend' => array( 'title' => 'ДЕМО · Обновление платформы самообслуживания', 'excerpt' => 'Пример карточки новости для проверки макета.', 'content' => 'Пример новости о развитии платформы: описание изменений, затронутые типы устройств и процесс обновления. Это не реальное объявление о выпуске версии.', 'meta' => array( 'isg_subtitle' => 'ALLVEND' ) ),
            'demo-news-skysend' => array( 'title' => 'ДЕМО · Интеграция нового поставщика услуг', 'excerpt' => 'Пример карточки новости для проверки макета.', 'content' => 'Пример новости о подключении внешнего провайдера, поддерживаемых сценариях, требованиях к тестированию и информации для партнёров. Материал создан только для демонстрации макета.', 'meta' => array( 'isg_subtitle' => 'SkySend' ) ),
            'demo-news-company' => array( 'title' => 'ДЕМО · Компания на отраслевом мероприятии', 'excerpt' => 'Пример новости о мероприятии; факты требуют подтверждения.', 'content' => 'Пример публикации о мероприятии с темой, основными тезисами и материалами участников. Дата, состав участников и цитаты требуют подтверждения.', 'meta' => array( 'isg_subtitle' => 'ISG / Новости' ) ),
            'demo-news-fastsys' => array( 'title' => 'ДЕМО · Обновление среды встраиваемого ПО', 'excerpt' => 'Пример карточки новости для проверки макета.', 'content' => 'Пример обзора изменений среды встраиваемого ПО и поддерживаемых конфигураций. Материал не описывает фактический выпуск продукта.', 'meta' => array( 'isg_subtitle' => 'FastSYS' ) ),
        ),
        'page' => array(
            'home' => array( 'title' => 'Главная', 'excerpt' => 'ДЕМО-страница. Визуальная композиция главной страницы находится в шаблоне темы front-page.php.', 'content' => 'ДЕМО-страница. Визуальная композиция главной страницы находится в шаблоне темы front-page.php.', 'meta' => array() ),
            'news' => array( 'title' => 'Новости', 'excerpt' => 'ДЕМО-страница новостей.', 'content' => 'ДЕМО-страница новостей. Публикации выводятся стандартным циклом WordPress.', 'meta' => array() ),
            'company' => array( 'title' => 'О компании', 'excerpt' => 'ДЕМО-страница раздела о компании.', 'content' => 'Здесь размещаются миссия, история, подход к разработке и направления деятельности. Замените этот текст утверждённой информацией о компании.', 'meta' => array() ),
            'contacts' => array( 'title' => 'Контакты', 'excerpt' => 'ДЕМО-страница. Контактная форма не подключена.', 'content' => 'ДЕМО-страница. Контактные данные заказчика и работающая форма обратной связи не подключены.', 'meta' => array() ),
            'privacy-policy' => array( 'title' => 'Политика конфиденциальности', 'excerpt' => 'Заглушка юридического текста.', 'content' => 'ЗАГЛУШКА. До публикации добавьте утверждённую политику конфиденциальности и обработки персональных данных.', 'meta' => array() ),
            'sitemap' => array( 'title' => 'Карта сайта', 'excerpt' => 'ДЕМО-карта сайта.', 'content' => '[isg_sitemap]', 'meta' => array() ),
            'support' => array( 'title' => 'Поддержка', 'excerpt' => 'ДЕМО-страница поддержки.', 'content' => 'Здесь размещаются загрузки, обратная связь, инструкции и материалы поддержки. Файлы и сценарии обращений должны быть согласованы с заказчиком.', 'meta' => array() ),
            'shop' => array( 'title' => 'Магазин', 'excerpt' => 'ДЕМО-каталог WooCommerce. Для полноценного перевода магазина требуется совместимый мультиязычный модуль.', 'content' => 'Каталог товаров. Для вывода магазина на русском языке требуется настроить интеграцию WooCommerce с используемым мультиязычным плагином. Для Polylang нужен отдельный модуль Polylang for WooCommerce; для WPML — WPML Multilingual & Multicurrency for WooCommerce. Эти модули не входят в тестовый архив.', 'meta' => array() ),
        ),
    );

    $created = 0;
    foreach ( $content as $post_type => $records ) {
        foreach ( $records as $slug => $translation ) {
            $created += isg_site_ensure_ru_demo_post( $post_type, $slug, $translation );
        }
    }

    $products = array(
        'solflare-card' => array( 'title' => 'Регистрация карты Solflare — пример услуги', 'excerpt' => 'ДЕМО. Цена и условия не заданы; оформление заказа отключено.', 'content' => 'ДЕМО-товар из задания. Solflare — сторонний продукт. Роль ISG, условия и цена не подтверждены; заказы остаются недоступны до согласования коммерческих условий и настройки оплаты.', 'demo_note' => 'ДЕМО-товар без цены. Solflare — сторонний продукт; роль ISG и условия не подтверждены.' ),
        'passive-income-setup' => array( 'title' => 'Настройка Web3-сервисов — пример услуги', 'excerpt' => 'ДЕМО. Доходность не гарантируется.', 'content' => 'ДЕМО-позиция каталога. Доходность не гарантируется. Цена, объём работ и поддерживаемые сервисы требуют согласования.', 'demo_note' => 'ДЕМО-товар без цены. Условия должны быть согласованы.' ),
        'allvend' => array( 'title' => 'ПО ALLVEND — запросить условия', 'excerpt' => 'ДЕМО. Цена и условия не заданы.', 'content' => 'ДЕМО-карточка товара. До публикации подтвердите текущую конфигурацию, лицензирование, цену, требования к устройствам и объём поставки.', 'demo_note' => 'ДЕМО-товар без цены. Не принимать заказы до утверждения условий.' ),
        'skysend' => array( 'title' => 'SkySend — запросить условия', 'excerpt' => 'ДЕМО. Цена и условия не заданы.', 'content' => 'ДЕМО-карточка системы. Цены, функциональная конфигурация, подключённые провайдеры и требования к внедрению не включены в тестовый контент.', 'demo_note' => 'ДЕМО-товар без цены. Не принимать заказы до утверждения условий.' ),
        'fastsys' => array( 'title' => 'FastSYS — запросить спецификацию', 'excerpt' => 'ДЕМО. Требуется подтверждение версии и лицензии.', 'content' => 'ДЕМО-карточка встраиваемого ПО. Подтвердите версию, поддерживаемые аппаратные конфигурации и условия лицензирования.', 'demo_note' => 'ДЕМО-товар без цены. Спецификация требует подтверждения.' ),
        'software-audit' => array( 'title' => 'Аудит IT-систем — пример услуги', 'excerpt' => 'ДЕМО. Стоимость зависит от объёма проекта.', 'content' => 'ДЕМО-услуга. Объём и стоимость следует определять исходя из масштаба проекта и согласовывать до начала работ.', 'demo_note' => 'ДЕМО-товар без цены. Условия должны быть согласованы.' ),
        'device-integration' => array( 'title' => 'Интеграция устройств — пример услуги', 'excerpt' => 'ДЕМО. Совместимость зависит от модели устройства.', 'content' => 'ДЕМО-услуга интеграции программного обеспечения и периферийного оборудования. Совместимость зависит от точной модели устройства и доступных интерфейсов.', 'demo_note' => 'ДЕМО-товар без цены. Условия должны быть согласованы.' ),
        'technical-consultation' => array( 'title' => 'Техническая консультация — пример услуги', 'excerpt' => 'ДЕМО. Цена и длительность не согласованы.', 'content' => 'ДЕМО-позиция каталога. Это не коммерческое предложение; стоимость и длительность консультации необходимо согласовать.', 'demo_note' => 'ДЕМО-товар без цены. Условия должны быть согласованы.' ),
    );
    foreach ( $products as $slug => $translation ) { $created += isg_site_ensure_ru_demo_product( $slug, $translation ); }

    // Seed a Russian menu and connect it to the English menu where supported.
    return $created;
}

/** Create or update only the missing Russian menu scaffolding; leave manual menus untouched. */
function isg_site_ensure_russian_demo_menu(): void {
    if ( ! function_exists( 'wp_get_nav_menu_object' ) || ! function_exists( 'wp_create_nav_menu' ) ) { return; }
    $menu_name = 'ISG Primary Menu (Demo) — RU';
    $menu = wp_get_nav_menu_object( $menu_name );
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( $menu_name );
    if ( is_wp_error( $menu_id ) || ! $menu_id ) { return; }
    if ( function_exists( 'pll_set_term_language' ) ) {
        $slugs = pll_languages_list( array( 'fields' => 'slug' ) );
        if ( is_array( $slugs ) && in_array( 'ru', $slugs, true ) ) { pll_set_term_language( (int) $menu_id, 'ru' ); }
    }
    $localized_shop = isg_site_demo_localized_permalink( 'page', 'shop' );
    $russian_home = function_exists( 'pll_home_url' ) ? (string) pll_home_url( 'ru' ) : home_url( '/ru/' );
    $items = array(
        array( 'Главная', $russian_home ),
        array( 'Решения', isg_site_localized_archive_url( 'isg_solution', 'products', 'ru' ) ),
        array( 'Услуги', isg_site_localized_archive_url( 'isg_service', 'services', 'ru' ) ),
        array( 'О компании', isg_site_demo_localized_permalink( 'page', 'company' ) ?: trailingslashit( $russian_home ) . 'company-ru/' ),
        array( 'Магазин', $localized_shop ?: ( function_exists( 'isg_theme_shop_url' ) ? isg_theme_shop_url() : trailingslashit( $russian_home ) . 'shop/' ) ),
    );
    $existing_items = (array) wp_get_nav_menu_items( $menu_id );
    foreach ( $items as $item ) {
        $found = null;
        foreach ( $existing_items as $existing_item ) {
            if ( $existing_item->title === $item[0] ) { $found = $existing_item; break; }
        }
        if ( $found ) {
            // This menu is generated by the plugin. Repair only the two known archive items.
            if ( in_array( $item[0], array( 'Решения', 'Услуги' ), true ) && $item[1] && $found->url !== $item[1] ) {
                wp_update_nav_menu_item( $menu_id, (int) $found->ID, array(
                    'menu-item-title' => $item[0], 'menu-item-type' => 'custom',
                    'menu-item-url' => $item[1], 'menu-item-status' => 'publish',
                ) );
            }
            continue;
        }
        wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $item[0], 'menu-item-type' => 'custom', 'menu-item-url' => $item[1], 'menu-item-status' => 'publish' ) );
    }
    // Link the translation to whichever populated menu is actually assigned to the primary location.
    $english = false;
    $locations = (array) get_theme_mod( 'nav_menu_locations', array() );
    if ( ! empty( $locations['primary'] ) ) { $english = wp_get_nav_menu_object( (int) $locations['primary'] ); }
    if ( ! $english ) { $english = wp_get_nav_menu_object( 'ISG Primary Menu (Demo)' ); }
    if ( $english ) { isg_site_link_demo_term_translations( 'nav_menu', (int) $english->term_id, (int) $menu_id ); }
}

function isg_site_demo_localized_permalink( string $post_type, string $english_slug ): string {
    $english = get_page_by_path( $english_slug, OBJECT, $post_type );
    if ( ! $english instanceof WP_Post ) { return ''; }
    if ( function_exists( 'pll_get_post' ) ) {
        $russian_id = (int) pll_get_post( $english->ID, 'ru' );
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $russian_id = (int) apply_filters( 'wpml_object_id', $english->ID, $post_type, false, 'ru' );
    } else {
        return '';
    }
    return $russian_id ? (string) get_permalink( $russian_id ) : '';
}

function isg_site_render_sitemap(): string {
    $out = '<div class="isg-sitemap">';
    $out .= '<section><h2>Основные разделы</h2><ul>';
    $out .= '<li><a href="' . esc_url( home_url( '/' ) ) . '">Главная</a></li>';
    foreach ( array( 'company', 'contacts', 'support', 'privacy-policy' ) as $page_slug ) {
        $site_page = get_page_by_path( $page_slug, OBJECT, 'page' );
        if ( $site_page instanceof WP_Post ) {
            $localized_id = function_exists( 'isg_theme_localized_post_id' ) ? isg_theme_localized_post_id( (int) $site_page->ID ) : (int) $site_page->ID;
            $localized_page = $localized_id ? get_post( $localized_id ) : null;
            if ( $localized_page instanceof WP_Post ) { $out .= '<li><a href="' . esc_url( get_permalink( $localized_page ) ) . '">' . esc_html( get_the_title( $localized_page ) ) . '</a></li>'; }
        }
    }
    foreach ( array( 'isg_solution' => 'Продукты и решения', 'isg_service' => 'Услуги', 'isg_vacancy' => 'Карьера', 'isg_partner' => 'Кейсы и внедрения' ) as $type => $label ) {
        $url = get_post_type_archive_link( $type );
        if ( $url ) { $out .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>'; }
    }
    if ( function_exists( 'isg_theme_shop_url' ) ) { $shop_url = isg_theme_shop_url(); } elseif ( function_exists( 'wc_get_page_id' ) && absint( wc_get_page_id( 'shop' ) ) && 'page' === get_post_type( absint( wc_get_page_id( 'shop' ) ) ) ) { $shop_url = get_permalink( absint( wc_get_page_id( 'shop' ) ) ); } else { $shop_url = home_url( '/shop/' ); }
    $out .= '<li><a href="' . esc_url( $shop_url ) . '">Магазин</a></li>';
    $out .= '</ul></section><section><h2>Публикации</h2><ul>';
    $posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 20, 'orderby' => 'date', 'order' => 'DESC' ) );
    foreach ( $posts as $post ) { $out .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>'; }
    $out .= '</ul></section></div>';
    return $out;
}

/** Public part: non-deceptive labels for seeded demo content. */
add_filter( 'post_class', 'isg_site_add_demo_post_class', 10, 3 );
function isg_site_add_demo_post_class( array $classes, array $class, int $post_id ): array {
    if ( '1' === get_post_meta( $post_id, 'isg_demo_content', true ) ) { $classes[] = 'isg-demo-record'; }
    return $classes;
}
add_action( 'woocommerce_before_shop_loop_item_title', 'isg_site_output_demo_product_badge', 8 );
add_action( 'woocommerce_before_single_product_summary', 'isg_site_output_demo_product_badge', 1 );
function isg_site_output_demo_product_badge(): void {
    if ( ! function_exists( 'is_product' ) || ! is_a( get_post(), 'WP_Post' ) ) { return; }
    if ( '1' === get_post_meta( get_the_ID(), 'isg_demo_content', true ) ) { echo '<span class="isg-demo-badge">ДЕМО · нет цены</span>'; }
}
add_action( 'woocommerce_single_product_summary', 'isg_site_demo_product_notice', 6 );
function isg_site_demo_product_notice(): void {
    if ( '1' !== get_post_meta( get_the_ID(), 'isg_demo_content', true ) ) { return; }
    echo '<p class="isg-demo-notice"><strong>Демо-карточка.</strong> Коммерческие условия не заданы. Заказ и оплата не доступны до согласования цены и настройки магазина.</p>';
}
