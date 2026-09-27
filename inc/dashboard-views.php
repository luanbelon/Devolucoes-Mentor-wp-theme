<?php
/**
 * Submenus e vistas do dashboard por tipo
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_b2b_dashboard_view_definitions(): array
{
    return [
        'dm-b2b-avaria' => [
            'canal'    => 'b2b',
            'label'    => 'Avaria',
            'scenario' => 'b2b_avaria',
        ],
        'dm-b2b-produto-falta' => [
            'canal'    => 'b2b',
            'label'    => 'Produto em falta',
            'scenario' => 'b2b_produto_falta',
        ],
        'dm-b2b-produto-errado' => [
            'canal'    => 'b2b',
            'label'    => 'Produto errado',
            'scenario' => 'b2b_produto_errado',
        ],
        'dm-b2b-produto-excesso' => [
            'canal'    => 'b2b',
            'label'    => 'Produto em excesso',
            'scenario' => 'b2b_produto_excesso',
        ],
        'dm-b2b-outro' => [
            'canal'    => 'b2b',
            'label'    => 'Outro',
            'scenario' => 'b2b_outro',
        ],
    ];
}

function devolucoes_mentor_b2c_dashboard_view_definitions(): array
{
    $tipos = devolucoes_mentor_tipos_pedido_admin();

    $slug_map = [
        'direito_livre_resolucao' => 'dm-b2c-livre-resolucao',
        'troca_comercial'         => 'dm-b2c-troca-comercial',
        'produto_defeituoso'      => 'dm-b2c-produto-defeituoso',
        'erro_expedicao'          => 'dm-b2c-erro-expedicao',
        'pedido_fora_prazo'       => 'dm-b2c-fora-prazo',
    ];

    $views = [];
    foreach ($tipos as $tipo_key => $label) {
        $slug = $slug_map[$tipo_key] ?? 'dm-b2c-' . sanitize_key($tipo_key);
        $views[$slug] = [
            'canal'              => 'b2c',
            'label'              => $label,
            'tipo_classificacao' => $tipo_key,
        ];
    }

    return $views;
}

function devolucoes_mentor_all_dashboard_views(): array
{
    return array_merge(
        devolucoes_mentor_b2b_dashboard_view_definitions(),
        devolucoes_mentor_b2c_dashboard_view_definitions()
    );
}

function devolucoes_mentor_get_dashboard_view_by_slug(string $page_slug): ?array
{
    $views = devolucoes_mentor_all_dashboard_views();

    if (!isset($views[$page_slug])) {
        return null;
    }

    return array_merge(['slug' => $page_slug], $views[$page_slug]);
}

function devolucoes_mentor_default_dashboard_slug(string $canal): string
{
    if ($canal === 'b2b') {
        return 'dm-b2b-avaria';
    }

    return 'dm-b2c-livre-resolucao';
}

function devolucoes_mentor_admin_menu_url_for_view(string $page_slug): string
{
    return admin_url('admin.php?page=' . $page_slug);
}

function devolucoes_mentor_row_matches_dashboard_view(array $row, array $view): bool
{
    if (($view['canal'] ?? '') !== ($row['canal'] ?? '')) {
        return false;
    }

    if (!empty($view['scenario'])) {
        $scenario = (string) ($row['b2b_scenario'] ?? '');
        if ($scenario === '') {
            $scenario = devolucoes_mentor_b2b_detect_scenario($row) ?? '';
        }

        return $scenario === $view['scenario'];
    }

    if (!empty($view['tipo_classificacao'])) {
        return (string) ($row['tipo_pedido'] ?? '') === $view['tipo_classificacao'];
    }

    return true;
}

function devolucoes_mentor_dashboard_columns_for_view(array $view): array
{
    if (!empty($view['scenario'])) {
        return devolucoes_mentor_b2b_dashboard_columns_for_scenario($view['scenario']);
    }

    if (!empty($view['tipo_classificacao'])) {
        return devolucoes_mentor_b2c_dashboard_columns_for_tipo($view['tipo_classificacao']);
    }

    return [];
}

function devolucoes_mentor_register_dashboard_menus(): void
{
    $b2b_views = devolucoes_mentor_b2b_dashboard_view_definitions();
    $b2c_views = devolucoes_mentor_b2c_dashboard_view_definitions();

    $register_canal_menus = static function (string $canal_label, array $views, int $position): void {
        if ($views === []) {
            return;
        }

        $slugs = array_keys($views);
        $parent_slug = $slugs[0];

        add_menu_page(
            $canal_label,
            $canal_label,
            'dm_access_devolucoes',
            $parent_slug,
            static function () use ($parent_slug): void {
                devolucoes_mentor_render_dashboard_page($parent_slug);
            },
            'dashicons-undo',
            $position
        );

        foreach ($views as $slug => $view) {
            // O 1.º submenu partilha o slug do menu pai — sem callback para não renderizar 2x.
            $callback = ($slug === $parent_slug)
                ? ''
                : static function () use ($slug): void {
                    devolucoes_mentor_render_dashboard_page($slug);
                };

            add_submenu_page(
                $parent_slug,
                $view['label'],
                $view['label'],
                'dm_access_devolucoes',
                $slug,
                $callback
            );
        }
    };

    $register_canal_menus(devolucoes_mentor_canal_nome('b2b'), $b2b_views, 26);

    if (!devolucoes_mentor_should_hide_apoio_cliente_for_current_user()) {
        $register_canal_menus(devolucoes_mentor_canal_nome('b2c'), $b2c_views, 27);
    }
}
add_action('admin_menu', 'devolucoes_mentor_register_dashboard_menus');

function devolucoes_mentor_render_dashboard_page(string $page_slug): void
{
    $view = devolucoes_mentor_get_dashboard_view_by_slug($page_slug);
    if (!$view) {
        wp_die('Página inválida.');
    }

    devolucoes_mentor_render_dashboard($view['canal'], $page_slug);
}

function devolucoes_mentor_redirect_legacy_dashboard_urls(): void
{
    global $pagenow;

    if ($pagenow !== 'admin.php' || empty($_GET['page'])) {
        return;
    }

    $page = sanitize_key($_GET['page']);
    $legacy = [
        'dm-devolucoes-b2b' => devolucoes_mentor_default_dashboard_slug('b2b'),
        'dm-devolucoes-b2c' => devolucoes_mentor_default_dashboard_slug('b2c'),
        'dm-dashboard-b2b'  => devolucoes_mentor_default_dashboard_slug('b2b'),
        'dm-dashboard-b2c'  => devolucoes_mentor_default_dashboard_slug('b2c'),
    ];

    if (isset($legacy[$page])) {
        $target = $legacy[$page];
        $target_view = devolucoes_mentor_get_dashboard_view_by_slug($target);
        if (
            ($target_view['canal'] ?? '') === 'b2c'
            && devolucoes_mentor_should_hide_apoio_cliente_for_current_user()
        ) {
            $target = devolucoes_mentor_default_dashboard_slug('b2b');
        }
        wp_safe_redirect(devolucoes_mentor_admin_menu_url_for_view($target));
        exit;
    }

    $view = devolucoes_mentor_get_dashboard_view_by_slug($page);
    if (
        $view
        && ($view['canal'] ?? '') === 'b2c'
        && devolucoes_mentor_should_hide_apoio_cliente_for_current_user()
    ) {
        wp_safe_redirect(devolucoes_mentor_admin_menu_url('b2b'));
        exit;
    }
}
add_action('admin_init', 'devolucoes_mentor_redirect_legacy_dashboard_urls', 0);

function devolucoes_mentor_redirect_cpt_list_to_dashboard(): void
{
    global $pagenow;

    if ($pagenow !== 'edit.php' || empty($_GET['post_type'])) {
        return;
    }

    $post_type = sanitize_key($_GET['post_type']);
    foreach (devolucoes_mentor_canais() as $canal => $config) {
        if ($config['post_type'] !== $post_type) {
            continue;
        }

        if ($canal === 'b2c' && devolucoes_mentor_should_hide_apoio_cliente_for_current_user()) {
            wp_safe_redirect(devolucoes_mentor_admin_menu_url('b2b'));
            exit;
        }

        wp_safe_redirect(devolucoes_mentor_admin_menu_url($canal));
        exit;
    }
}
add_action('admin_init', 'devolucoes_mentor_redirect_cpt_list_to_dashboard', 1);
