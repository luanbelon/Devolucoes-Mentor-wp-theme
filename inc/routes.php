<?php
/**
 * Rotas públicas — páginas B2B/B2C e redirect da home
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_redirect_external(string $url, int $status = 302): void
{
    wp_redirect($url, $status);
    exit;
}

function devolucoes_mentor_is_site_root_request(): bool
{
    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $path = (string) wp_parse_url($uri, PHP_URL_PATH);
    $path = untrailingslashit($path);

    return $path === '' || $path === '/';
}

function devolucoes_mentor_redirect_home(): void
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }

    if (devolucoes_mentor_is_site_root_request() || is_front_page() || is_home()) {
        devolucoes_mentor_redirect_external('https://mentor-company.pt/', 302);
    }
}
add_action('template_redirect', 'devolucoes_mentor_redirect_home', 1);

function devolucoes_mentor_setup_pages(): void
{
    $pages = [
        'b2b' => [
            'title'    => 'Pós Venda',
            'template' => 'page-b2b.php',
        ],
        'b2c' => [
            'title'    => 'Apoio Cliente',
            'template' => 'page-b2c.php',
        ],
    ];

    foreach ($pages as $slug => $page) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            if (get_post_field('post_title', $existing->ID) !== $page['title']) {
                wp_update_post([
                    'ID'         => $existing->ID,
                    'post_title' => $page['title'],
                ]);
            }

            if (get_page_template_slug($existing->ID) !== $page['template']) {
                update_post_meta($existing->ID, '_wp_page_template', $page['template']);
            }
            continue;
        }

        $page_id = wp_insert_post([
            'post_title'   => $page['title'],
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ], true);

        if (!is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', $page['template']);
        }
    }
}
add_action('after_switch_theme', 'devolucoes_mentor_setup_pages');
add_action('admin_init', 'devolucoes_mentor_setup_pages');

function devolucoes_mentor_get_canal_from_request(): string
{
    if (!empty($_POST['canal']) && in_array($_POST['canal'], ['b2b', 'b2c'], true)) {
        return sanitize_key($_POST['canal']);
    }

    if (is_page('b2c')) {
        return 'b2c';
    }

    return 'b2b';
}
