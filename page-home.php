<?php
/**
 * Legacy home template — redireciona para mentor-company.pt
 */

require_once get_template_directory() . '/inc/routes.php';
devolucoes_mentor_redirect_external('https://mentor-company.pt/', 302);
