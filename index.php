<?php
/**
 * Fallback — redireciona para mentor-company.pt
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/routes.php';
devolucoes_mentor_redirect_external('https://mentor-company.pt/', 302);
