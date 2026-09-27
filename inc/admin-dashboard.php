<?php
/**
 * Admin Dashboard — tabela + Kanban (B2B e B2C)
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_get_dashboard_rows(string $canal, string $view_slug = ''): array
{
    $config = devolucoes_mentor_canal_config($canal);
    if (!$config) {
        return [];
    }

    $view = $view_slug !== '' ? devolucoes_mentor_get_dashboard_view_by_slug($view_slug) : null;

    $posts = get_posts([
        'post_type'      => $config['post_type'],
        'post_status'    => ['private', 'publish', 'draft'],
        'posts_per_page' => 500,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $rows = [];
    foreach ($posts as $post) {
        devolucoes_mentor_maybe_backfill_pedido((int) $post->ID);
        $row = devolucoes_mentor_get_pedido_row((int) $post->ID);

        if ($view && !devolucoes_mentor_row_matches_dashboard_view($row, $view)) {
            continue;
        }

        $rows[] = $row;
    }

    return $rows;
}

function devolucoes_mentor_enqueue_admin_assets(string $hook): void
{
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    $view = devolucoes_mentor_get_dashboard_view_by_slug($page);

    if (!$view) {
        return;
    }

    $canal = $view['canal'];
    $config = devolucoes_mentor_canal_config($canal);

    $admin_css = DEVOLUCOES_MENTOR_DIR . '/assets/css/admin-dashboard.css';
    $admin_js  = DEVOLUCOES_MENTOR_DIR . '/assets/js/admin-dashboard.js';

    wp_enqueue_style(
        'devolucoes-mentor-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/css/admin-dashboard.css',
        [],
        file_exists($admin_css) ? (string) filemtime($admin_css) : DEVOLUCOES_MENTOR_VERSION
    );

    wp_enqueue_editor();

    wp_enqueue_script(
        'devolucoes-mentor-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/js/admin-dashboard.js',
        ['jquery', 'wp-editor', 'editor'],
        file_exists($admin_js) ? (string) filemtime($admin_js) : DEVOLUCOES_MENTOR_VERSION,
        true
    );

    $view_slug = $view['slug'];
    $rows = devolucoes_mentor_get_dashboard_rows($canal, $view_slug);
    $columns = devolucoes_mentor_dashboard_columns($canal, $view_slug);

    $viewScenario = $view['scenario'] ?? '';
    $viewTipo = $view['tipo_classificacao'] ?? '';

    wp_localize_script('devolucoes-mentor-admin', 'dmDashboard', [
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('dm_dashboard'),
        'canal'         => $canal,
        'viewSlug'      => $view_slug,
        'viewLabel'     => $view['label'],
        'viewScenario'  => $viewScenario,
        'viewTipo'      => $viewTipo,
        'label'         => $config['label'],
        'colCount'      => count($columns),
        'columns'       => $columns,
        'rows'          => $rows,
        'tipos'         => devolucoes_mentor_tipos_pedido_admin(),
        'estados'       => devolucoes_mentor_estados_for_canal($canal),
        'equipe'        => devolucoes_mentor_equipe_users(),
        'finais'        => devolucoes_mentor_estados_finais(),
        'emailTipoMap'  => devolucoes_mentor_email_tipo_map_for_js(),
        'b2bEmailRules' => devolucoes_mentor_b2b_email_rules(),
        'subtipoLabels' => devolucoes_mentor_subtipos_erro_labels(),
        'categoriasOutro' => devolucoes_mentor_b2b_categorias_outro(),
        'showReembolsos' => $canal === 'b2c',
        'emailSendingDisabled' => devolucoes_mentor_equipe_email_sending_disabled(),
        'canDelete'     => devolucoes_mentor_can_delete_pedidos($canal),
    ]);
}
add_action('admin_enqueue_scripts', 'devolucoes_mentor_enqueue_admin_assets');

function devolucoes_mentor_render_dashboard(string $canal = 'b2b', string $view_slug = ''): void
{
    $config = devolucoes_mentor_canal_config($canal);
    if (!$config || !devolucoes_mentor_can_manage_pedidos($canal)) {
        wp_die('Sem permissão.');
    }

    if ($view_slug === '') {
        $view_slug = devolucoes_mentor_default_dashboard_slug($canal);
    }

    $view = devolucoes_mentor_get_dashboard_view_by_slug($view_slug);
    if (!$view || $view['canal'] !== $canal) {
        wp_die('Página inválida.');
    }

    $columns = devolucoes_mentor_dashboard_columns($canal, $view_slug);
    $rows = devolucoes_mentor_get_dashboard_rows($canal, $view_slug);
    $tipos = devolucoes_mentor_tipos_pedido_admin();
    $estados = devolucoes_mentor_estados_for_canal($canal);
    $equipe = devolucoes_mentor_equipe_users();
    $col_count = count($columns);
    $show_reembolsos = $canal === 'b2c';
    ?>
    <div class="wrap dm-wrap" data-canal="<?php echo esc_attr($canal); ?>" data-view="<?php echo esc_attr($view_slug); ?>">
        <div class="dm-header">
            <div>
                <h1 class="dm-header__title"><?php echo esc_html($view['label']); ?></h1>
                <p class="dm-header__sub">Pedidos de <?php echo esc_html($config['label']); ?> — gestão operacional</p>
            </div>
        </div>

        <nav class="dm-tabs" role="tablist">
            <button type="button" class="dm-tabs__btn is-active" data-view="dashboard" role="tab" aria-selected="true">
                <span class="dm-tabs__icon" aria-hidden="true">▦</span>
                Dashboard
            </button>
            <button type="button" class="dm-tabs__btn" data-view="kanban" role="tab" aria-selected="false">
                <span class="dm-tabs__icon" aria-hidden="true">▥</span>
                Kanban por Estado
            </button>
            <?php if ($show_reembolsos) : ?>
            <button type="button" class="dm-tabs__btn" data-view="reembolsos" role="tab" aria-selected="false">
                <span class="dm-tabs__icon" aria-hidden="true">◇</span>
                Reembolsos Pendentes
            </button>
            <?php endif; ?>
        </nav>

        <div class="dm-toolbar">
            <input type="search" id="dm-search" class="dm-search" placeholder="Pesquisar ID, cliente, SKU, email…">
            <div class="dm-toolbar__meta">
                <span id="dm-count"><?php echo esc_html((string) count($rows)); ?></span> pedidos
            </div>
        </div>

        <section id="dm-view-dashboard" class="dm-view is-active">
            <div class="dm-table-scroll">
                <table class="dm-table" id="dm-table">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column) : ?>
                                <th<?php echo ($column['type'] ?? '') === 'actions' ? ' class="dm-th-actions"' : ''; ?>><?php echo esc_html($column['label']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="dm-table-body">
                        <?php if ($rows === []) : ?>
                            <tr>
                                <td colspan="<?php echo esc_attr((string) $col_count); ?>">
                                    <div class="dm-empty">Nenhum pedido encontrado.</div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($rows as $row) : ?>
                                <?php devolucoes_mentor_dashboard_render_table_row($row, $canal, $tipos, $estados, $equipe, $view_slug); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="dm-view-kanban" class="dm-view" hidden>
            <div class="dm-kanban" id="dm-kanban"></div>
        </section>

        <?php if ($show_reembolsos) : ?>
        <section id="dm-view-reembolsos" class="dm-view" hidden>
            <div class="dm-table-scroll">
                <table class="dm-table" id="dm-table-reembolsos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Valor</th>
                            <th>Data do reembolso</th>
                            <th>Nota de crédito</th>
                            <?php if (devolucoes_mentor_can_delete_pedidos($canal)) : ?>
                                <th class="dm-th-actions">Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="dm-reembolsos-body"></tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
    </div>
    <?php
}

function devolucoes_mentor_ajax_update_field(): void
{
    check_ajax_referer('dm_dashboard', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    $field   = sanitize_key($_POST['field'] ?? '');
    $value   = wp_unslash($_POST['value'] ?? '');

    if (!$post_id) {
        wp_send_json_error(['message' => 'Pedido inválido.']);
    }

    $post_type = get_post_type($post_id);
    if (!in_array($post_type, devolucoes_mentor_post_types(), true)) {
        wp_send_json_error(['message' => 'Pedido inválido.']);
    }

    $canal = devolucoes_mentor_canal_for_post_type($post_type);
    if (!devolucoes_mentor_can_manage_pedidos($canal)) {
        wp_send_json_error(['message' => 'Sem permissão.'], 403);
    }

    $allowed = [
        'tipo_classificacao'   => 'select_tipo',
        'estado'               => 'select_estado',
        'responsavel'          => 'select_user',
        'valor'                => 'text',
        'num_encomenda'        => 'text',
        'num_recolha'          => 'text',
        'observacoes'          => 'textarea',
        'motivo'               => 'textarea',
        'nota_credito_emitida' => 'bool',
        'vale_emitido'         => 'bool',
        'cliente_fica_produtos'=> 'bool',
        'produto_falta_confirmado' => 'bool',
        'pedido_envio'         => 'bool',
        'nota_credito_num'     => 'text',
        'fatura_num'           => 'text',
        'vale_codigo'          => 'text',
        'data_rececao_smartlo' => 'date',
        'data_reembolso'       => 'date',
        'sku'                  => 'text',
        'nome_cliente'         => 'text',
        'email_cliente'        => 'email',
        'b2b_categoria'        => 'select_categoria',
    ];

    $field_map = [
        'tipo_pedido'          => 'tipo_classificacao',
        'nota_credito_emitida' => 'nota_credito_emitida',
        'vale_emitido'         => 'vale_emitido',
        'cliente_fica_produtos'=> 'cliente_fica_produtos',
        'produto_falta_confirmado' => 'produto_falta_confirmado',
        'pedido_envio'         => 'pedido_envio',
        'nota_credito_num'     => 'nota_credito_num',
        'fatura_num'           => 'fatura_num',
        'vale_codigo'          => 'vale_codigo',
        'data_rececao_smartlo' => 'data_rececao_smartlo',
        'data_reembolso'       => 'data_reembolso',
        'num_encomenda'        => 'num_encomenda',
        'num_recolha'          => 'num_recolha',
        'observacoes'          => 'observacoes',
        'motivo'               => 'motivo',
        'estado'               => 'estado',
        'responsavel'          => 'responsavel',
        'valor'                => 'valor',
        'sku'                  => 'sku',
        'nome_cliente'         => 'nome_cliente',
        'email_cliente'        => 'email_cliente',
        'b2b_categoria'        => 'b2b_categoria',
    ];

    if (!isset($field_map[$field])) {
        wp_send_json_error(['message' => 'Campo não editável.']);
    }

    $meta_key = $field_map[$field];
    $type = $allowed[$meta_key] ?? 'text';

    switch ($type) {
        case 'bool':
            $clean = ($value === '1' || $value === 'true' || $value === true) ? '1' : '0';
            break;
        case 'select_tipo':
            $tipos = devolucoes_mentor_tipos_pedido_admin();
            if ($value !== '' && !array_key_exists($value, $tipos)) {
                wp_send_json_error(['message' => 'Tipo inválido.']);
            }
            $clean = sanitize_text_field($value);
            break;
        case 'select_estado':
            $estados = devolucoes_mentor_estados_for_canal($canal);
            if ($value !== '' && !array_key_exists($value, $estados)) {
                wp_send_json_error(['message' => 'Estado inválido.']);
            }
            $clean = sanitize_text_field($value);
            if (in_array($clean, devolucoes_mentor_estados_finais(), true)) {
                $existing_fim = get_post_meta($post_id, '_dm_data_finalizacao', true);
                if (!$existing_fim) {
                    update_post_meta($post_id, '_dm_data_finalizacao', current_time('mysql'));
                }
            } else {
                delete_post_meta($post_id, '_dm_data_finalizacao');
            }
            break;
        case 'select_user':
            $equipe = devolucoes_mentor_equipe_users();
            if ($value !== '' && !isset($equipe[$value])) {
                wp_send_json_error(['message' => 'Responsável inválido.']);
            }
            $clean = sanitize_text_field($value);
            break;
        case 'select_categoria':
            if ($canal !== 'b2b' || !array_key_exists($value, devolucoes_mentor_b2b_categorias_outro())) {
                wp_send_json_error(['message' => 'Categoria inválida.']);
            }
            $clean = sanitize_key($value);
            update_post_meta($post_id, '_dm_b2b_scenario', $clean);
            break;
        case 'email':
            $clean = sanitize_email($value);
            break;
        case 'date':
            $clean = sanitize_text_field($value);
            if ($clean !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}/', $clean)) {
                wp_send_json_error(['message' => 'Data inválida.']);
            }
            break;
        case 'textarea':
            $clean = sanitize_textarea_field($value);
            break;
        default:
            $clean = sanitize_text_field($value);
    }

    update_post_meta($post_id, '_dm_' . $meta_key, $clean);

    wp_send_json_success([
        'row' => devolucoes_mentor_get_pedido_row($post_id),
    ]);
}
add_action('wp_ajax_dm_update_field', 'devolucoes_mentor_ajax_update_field');

function devolucoes_mentor_uploads_path_from_url(string $url): string
{
    $upload = wp_upload_dir();
    $url = strtok($url, '?');
    $candidates = [
        $upload['baseurl'],
        set_url_scheme($upload['baseurl'], 'https'),
        set_url_scheme($upload['baseurl'], 'http'),
    ];

    foreach ($candidates as $base) {
        if ($base !== '' && str_starts_with($url, $base)) {
            return wp_normalize_path($upload['basedir'] . substr($url, strlen($base)));
        }
    }

    return '';
}

function devolucoes_mentor_delete_submission_files(int $post_id): void
{
    $upload = wp_upload_dir();
    $base_dir = wp_normalize_path(trailingslashit($upload['basedir']) . 'devolucoes/');
    $dirs = [];

    foreach (['fotografias', 'videos', 'documentos'] as $group) {
        $urls = json_decode((string) get_post_meta($post_id, '_dm_' . $group, true) ?: '[]', true);
        if (!is_array($urls)) {
            continue;
        }

        foreach ($urls as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }

            $path = devolucoes_mentor_uploads_path_from_url($url);
            if ($path === '' || !str_starts_with($path, $base_dir) || !is_file($path)) {
                continue;
            }

            wp_delete_file($path);
            $dirs[dirname($path)] = true;
        }
    }

    foreach (array_keys($dirs) as $dir) {
        $normalized = wp_normalize_path($dir);
        if (!str_starts_with($normalized, $base_dir) || !is_dir($normalized)) {
            continue;
        }

        $remaining = glob($normalized . '/*');
        if ($remaining === false || $remaining === []) {
            rmdir($normalized);
        }
    }
}

function devolucoes_mentor_ajax_delete_pedido(): void
{
    check_ajax_referer('dm_dashboard', 'nonce');

    $post_id = absint($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error(['message' => 'Pedido inválido.']);
    }

    $post_type = get_post_type($post_id);
    if (!in_array($post_type, devolucoes_mentor_post_types(), true)) {
        wp_send_json_error(['message' => 'Pedido inválido.']);
    }

    if (!devolucoes_mentor_can_delete_pedido($post_id)) {
        wp_send_json_error(['message' => 'Sem permissão para excluir este pedido.'], 403);
    }

    devolucoes_mentor_delete_submission_files($post_id);

    $deleted = wp_delete_post($post_id, true);
    if (!$deleted) {
        wp_send_json_error(['message' => 'Não foi possível excluir o pedido.']);
    }

    wp_send_json_success(['message' => 'Pedido excluído.']);
}
add_action('wp_ajax_dm_delete_pedido', 'devolucoes_mentor_ajax_delete_pedido');

function devolucoes_mentor_maybe_backfill_pedido(int $post_id): void
{
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, devolucoes_mentor_post_types(), true)) {
        return;
    }

    $canal = devolucoes_mentor_canal_for_post_type($post_type);
    if (!get_post_meta($post_id, '_dm_canal', true)) {
        update_post_meta($post_id, '_dm_canal', $canal);
    }

    if (!get_post_meta($post_id, '_dm_pedido_id', true)) {
        $marca = (string) get_post_meta($post_id, '_dm_marca', true);
        update_post_meta($post_id, '_dm_pedido_id', devolucoes_mentor_generate_pedido_id($marca ?: 'XX'));
    }

    if (!get_post_meta($post_id, '_dm_estado', true)) {
        update_post_meta($post_id, '_dm_estado', 'pedido_recebido');
    }

    if (!get_post_meta($post_id, '_dm_sku', true)) {
        $ref = get_post_meta($post_id, '_dm_referencia', true);
        if ($ref) {
            update_post_meta($post_id, '_dm_sku', $ref);
        }
    }

    if (!get_post_meta($post_id, '_dm_nome_cliente', true)) {
        $contacto = get_post_meta($post_id, '_dm_contacto', true);
        if ($contacto) {
            update_post_meta($post_id, '_dm_nome_cliente', $contacto);
        }
    }

    if (!get_post_meta($post_id, '_dm_email_cliente', true)) {
        $email = get_post_meta($post_id, '_dm_email', true);
        if ($email) {
            update_post_meta($post_id, '_dm_email_cliente', $email);
        }
    }

    if (!get_post_meta($post_id, '_dm_contacto', true)) {
        $nome = get_post_meta($post_id, '_dm_nome_cliente', true);
        if ($nome) {
            update_post_meta($post_id, '_dm_contacto', $nome);
        }
    }

    if (!get_post_meta($post_id, '_dm_referencia', true)) {
        $sku = get_post_meta($post_id, '_dm_sku', true);
        if ($sku) {
            update_post_meta($post_id, '_dm_referencia', $sku);
        }
    }

    if (!get_post_meta($post_id, '_dm_descricao', true)) {
        $obs = get_post_meta($post_id, '_dm_observacoes', true);
        if ($obs) {
            update_post_meta($post_id, '_dm_descricao', $obs);
        }
    }

    if (!get_post_meta($post_id, '_dm_motivo', true)) {
        $tipo = get_post_meta($post_id, '_dm_tipo_pedido', true);
        if ($tipo) {
            update_post_meta($post_id, '_dm_motivo', $tipo);
        }
    }

    if (!get_post_meta($post_id, '_dm_observacoes', true)) {
        $desc = get_post_meta($post_id, '_dm_descricao', true);
        if ($desc) {
            update_post_meta($post_id, '_dm_observacoes', $desc);
        }
    }

    if (!get_post_meta($post_id, '_dm_tipo_pedido_key', true)) {
        $row = devolucoes_mentor_get_pedido_row($post_id);
        $tipo_key = devolucoes_mentor_detect_tipo_pedido_key($row);
        if ($tipo_key !== '') {
            update_post_meta($post_id, '_dm_tipo_pedido_key', $tipo_key);
        }
    }

    if ($canal === 'b2b') {
        $row = devolucoes_mentor_get_pedido_row($post_id);
        $scenario = devolucoes_mentor_b2b_detect_scenario($row);
        if ($scenario) {
            update_post_meta($post_id, '_dm_b2b_scenario', $scenario);
        }

        $subtipo_key = devolucoes_mentor_row_subtipo_key($row);
        if ($subtipo_key !== '' && $row['subtipo'] === '') {
            $labels = devolucoes_mentor_subtipos_erro_labels();
            if (isset($labels[$subtipo_key])) {
                update_post_meta($post_id, '_dm_subtipo', $labels[$subtipo_key]);
            }
        }
    }
}

function devolucoes_mentor_backfill_on_dashboard(): void
{
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if ($page === '') {
        return;
    }

    $view = devolucoes_mentor_get_dashboard_view_by_slug($page);
    if (!$view || !devolucoes_mentor_can_manage_pedidos($view['canal'])) {
        return;
    }

    $config = devolucoes_mentor_canal_config($view['canal']);
    $ids = get_posts([
        'post_type'      => $config['post_type'],
        'post_status'    => 'any',
        'posts_per_page' => 500,
        'fields'         => 'ids',
    ]);

    foreach ($ids as $id) {
        devolucoes_mentor_maybe_backfill_pedido((int) $id);
    }
}

add_action('admin_init', static function (): void {
    if (!is_admin()) {
        return;
    }

    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if ($page === '' || !devolucoes_mentor_get_dashboard_view_by_slug($page)) {
        return;
    }

    devolucoes_mentor_backfill_on_dashboard();
}, 5);
