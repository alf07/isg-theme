<?php
/** Explicit module loading for ISG site functionality. */
defined( 'ABSPATH' ) || exit;
require_once ISG_SITE_PATH . 'includes/content/post-types.php';
require_once ISG_SITE_PATH . 'includes/content/taxonomies.php';
require_once ISG_SITE_PATH . 'includes/content/meta-boxes.php';
require_once ISG_SITE_PATH . 'includes/admin/integrations-demo.php';
require_once ISG_SITE_PATH . 'includes/admin/site-settings.php';
require_once ISG_SITE_PATH . 'includes/integrations/providers.php';
require_once ISG_SITE_PATH . 'includes/integrations/multilingual.php';

require_once ISG_SITE_PATH . 'includes/admin/demo-content.php';
