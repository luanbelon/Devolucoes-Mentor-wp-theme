<?php
/**
 * Schema — opções, prefixos e helpers de pedidos
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_canal_nome(string $canal): string
{
    return match ($canal) {
        'b2b'   => 'Pós Venda',
        'b2c'   => 'Apoio Cliente',
        default => $canal,
    };
}

function devolucoes_mentor_canais(): array
{
    return [
        'b2b' => [
            'post_type'       => 'pedido_devolucao',
            'label'           => devolucoes_mentor_canal_nome('b2b'),
            'menu_name'       => devolucoes_mentor_canal_nome('b2b'),
            'page_slug'       => 'b2b',
            'admin_menu_slug' => 'dm-b2b-avaria',
            'capability'      => 'edit_pedidos_devolucao',
        ],
        'b2c' => [
            'post_type'       => 'pedido_devolucao_b2c',
            'label'           => devolucoes_mentor_canal_nome('b2c'),
            'menu_name'       => devolucoes_mentor_canal_nome('b2c'),
            'page_slug'       => 'b2c',
            'admin_menu_slug' => 'dm-b2c-livre-resolucao',
            'capability'      => 'edit_pedidos_devolucao_b2c',
        ],
    ];
}

function devolucoes_mentor_admin_menu_slug(string $canal): string
{
    $config = devolucoes_mentor_canal_config($canal);
    return $config['admin_menu_slug'] ?? 'dm-devolucoes-' . $canal;
}

function devolucoes_mentor_admin_menu_url(string $canal): string
{
    return devolucoes_mentor_admin_menu_url_for_view(devolucoes_mentor_default_dashboard_slug($canal));
}

function devolucoes_mentor_post_types(): array
{
    return array_column(devolucoes_mentor_canais(), 'post_type');
}

function devolucoes_mentor_canal_config(string $canal): ?array
{
    $canais = devolucoes_mentor_canais();
    return $canais[$canal] ?? null;
}

function devolucoes_mentor_post_type_for_canal(string $canal): string
{
    $config = devolucoes_mentor_canal_config($canal);
    return $config['post_type'] ?? 'pedido_devolucao';
}

function devolucoes_mentor_canal_for_post_type(string $post_type): string
{
    foreach (devolucoes_mentor_canais() as $canal => $config) {
        if ($config['post_type'] === $post_type) {
            return $canal;
        }
    }

    return 'b2b';
}

function devolucoes_mentor_tipos_pedido_admin(): array
{
    return [
        'direito_livre_resolucao' => 'Direito de Livre Resolução (14 dias)',
        'troca_comercial'         => 'Troca Comercial',
        'produto_defeituoso'      => 'Produto Defeituoso',
        'erro_expedicao'          => 'Erro de Expedição',
        'pedido_fora_prazo'       => 'Pedido Fora de Prazo',
    ];
}

function devolucoes_mentor_estados(): array
{
    return [
        'pedido_recebido'              => 'Pedido Recebido',
        'aguarda_info_cliente'         => 'Aguarda Informação do Cliente',
        'aguarda_envio_cliente'        => 'Aguarda Envio do Cliente',
        'recolha_agendada'             => 'Recolha Agendada',
        'produto_recebido_smartlo'     => 'Produto Recebido (Smartlo)',
        'em_verificacao'               => 'Em Verificação',
        'aguardar_reembolso_financeiro'=> 'Aguardar Reembolso Financeiro',
        'vale_emitido'                 => 'Vale Emitido',
        'processo_concluido'           => 'Processo Concluído',
        'recusado'                     => 'Recusado',
    ];
}

function devolucoes_mentor_estados_finais(): array
{
    return ['processo_concluido', 'recusado'];
}

function devolucoes_mentor_estados_for_canal(string $canal): array
{
    if ($canal === 'b2b') {
        return devolucoes_mentor_b2b_estados();
    }

    return devolucoes_mentor_estados();
}

function devolucoes_mentor_marcas(): array
{
    return ['Owlet', 'Rockit', 'Kit & Kin', 'ZAZU', 'LatchLight', 'Shnuggle', 'Real Shades', 'BabyDan', 'Neno'];
}

function devolucoes_mentor_tipos_pedido_form(string $canal = ''): array
{
    if ($canal === 'b2b') {
        return [
            'avaria_cliente' => 'Avaria reportada por cliente final',
            'erro_encomenda' => 'Erro na encomenda',
            'outro'          => 'Outro',
        ];
    }

    return [
        'avaria_cliente' => 'Avaria reportada por cliente final',
        'erro_encomenda' => 'Erro na encomenda',
        'defeito'        => 'Produto com defeito',
        'outro'          => 'Outro',
    ];
}

function devolucoes_mentor_subtipos_erro(string $canal = ''): array
{
    return devolucoes_mentor_subtipos_erro_form($canal);
}

function devolucoes_mentor_subtipos_erro_form(string $canal = ''): array
{
    return devolucoes_mentor_subtipos_erro_labels();
}

function devolucoes_mentor_marca_prefix(string $marca): string
{
    $map = [
        'Kit & Kin'   => 'KK',
        'Owlet'       => 'OW',
        'Rockit'      => 'RR',
        'ZAZU'        => 'ZZ',
        'LatchLight'  => 'LL',
        'Shnuggle'    => 'SH',
        'Real Shades' => 'RS',
        'BabyDan'     => 'BD',
        'Neno'        => 'NE',
    ];

    if (isset($map[$marca])) {
        return $map[$marca];
    }

    $clean = preg_replace('/[^A-Za-z]/', '', $marca);
    return strtoupper(substr($clean ?: 'XX', 0, 2));
}

function devolucoes_mentor_generate_pedido_id(string $marca): string
{
    $year   = current_time('Y');
    $prefix = devolucoes_mentor_marca_prefix($marca);

    do {
        $number = (string) random_int(10000, 99999);
        $id     = sprintf('DEV-%s-%s-%s', $year, $prefix, $number);
        $exists = get_posts([
            'post_type'      => devolucoes_mentor_post_types(),
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_dm_pedido_id',
            'meta_value'     => $id,
        ]);
    } while (!empty($exists));

    return $id;
}

function devolucoes_mentor_map_form_tipo_to_motivo(string $tipo_label, string $subtipo): string
{
    return $subtipo !== '' ? $tipo_label . ' — ' . $subtipo : $tipo_label;
}

function devolucoes_mentor_dias_em_processo(string $data_inicio, string $estado, string $data_fim = ''): int
{
    if ($data_inicio === '') {
        return 0;
    }

    $start = strtotime($data_inicio);
    if (!$start) {
        return 0;
    }

    $end_ts = time();
    if (in_array($estado, devolucoes_mentor_estados_finais(), true) && $data_fim !== '') {
        $parsed = strtotime($data_fim);
        if ($parsed) {
            $end_ts = $parsed;
        }
    }

    $days = (int) floor(($end_ts - $start) / DAY_IN_SECONDS);
    return max(0, $days);
}

function devolucoes_mentor_equipe_users(): array
{
    $users = get_users([
        'role__in' => ['equipe_mentor', 'administrator'],
        'orderby'  => 'display_name',
        'order'    => 'ASC',
    ]);

    $list = [];
    foreach ($users as $user) {
        $list[(string) $user->ID] = $user->display_name;
    }

    return $list;
}

function devolucoes_mentor_decode_file_urls(int $post_id, string $key): array
{
    $raw = get_post_meta($post_id, '_dm_' . $key, true);
    $urls = json_decode(is_string($raw) ? $raw : '[]', true);

    if (!is_array($urls)) {
        return [];
    }

    return array_values(array_filter($urls, static function ($url): bool {
        return is_string($url) && $url !== '';
    }));
}

function devolucoes_mentor_get_pedido_row(int $post_id): array
{
    $meta = static function (string $key) use ($post_id): string {
        return (string) get_post_meta($post_id, '_dm_' . $key, true);
    };

    $estado = $meta('estado');
    $data_pedido = $meta('data_submissao');
    $data_fim = $meta('data_finalizacao');

    $row = [
        'post_id'                => $post_id,
        'canal'                  => $meta('canal') ?: devolucoes_mentor_canal_for_post_type(get_post_type($post_id)),
        'pedido_id'              => $meta('pedido_id'),
        'data_pedido'            => $data_pedido,
        'empresa'                => $meta('empresa'),
        'nif'                    => $meta('nif'),
        'contacto'               => $meta('contacto'),
        'telefone'               => $meta('telefone'),
        'tipo_pedido_key'        => $meta('tipo_pedido_key'),
        'tipo_pedido_form'       => $meta('tipo_pedido'),
        'subtipo'                => $meta('subtipo'),
        'referencia'             => $meta('referencia') ?: $meta('sku'),
        'quantidade'             => $meta('quantidade'),
        'descricao'              => $meta('descricao'),
        'fotografias'            => devolucoes_mentor_decode_file_urls($post_id, 'fotografias'),
        'videos'                 => devolucoes_mentor_decode_file_urls($post_id, 'videos'),
        'documentos'             => devolucoes_mentor_decode_file_urls($post_id, 'documentos'),
        'motivo'                 => $meta('motivo'),
        'marca'                  => $meta('marca'),
        'sku'                    => $meta('sku') ?: $meta('referencia'),
        'num_encomenda'          => $meta('num_encomenda'),
        'nome_cliente'           => $meta('nome_cliente') ?: $meta('contacto'),
        'email_cliente'          => $meta('email_cliente') ?: $meta('email'),
        'tipo_pedido'            => $meta('tipo_classificacao'),
        'estado'                 => $estado,
        'responsavel'            => $meta('responsavel'),
        'valor'                  => $meta('valor'),
        'dias_em_processo'       => devolucoes_mentor_dias_em_processo($data_pedido, $estado, $data_fim),
        'email_recebimento_enviado' => $meta('email_recebimento_enviado') === '1',
        'email_1_enviado'        => $meta('email_1_enviado') === '1',
        'data_rececao_smartlo'   => $meta('data_rececao_smartlo'),
        'email_2_enviado'        => $meta('email_2_enviado') === '1',
        'data_reembolso'         => $meta('data_reembolso'),
        'nota_credito_emitida'   => $meta('nota_credito_emitida') === '1',
        'email_3_enviado'        => $meta('email_3_enviado') === '1',
        'email_4_enviado'        => $meta('email_4_enviado') === '1',
        'email_5_enviado'        => $meta('email_5_enviado') === '1',
        'email_6_enviado'        => $meta('email_6_enviado') === '1',
        'num_recolha'            => $meta('num_recolha'),
        'observacoes'            => $meta('observacoes'),
        'vale_emitido'           => $meta('vale_emitido') === '1',
        'nota_credito_num'       => $meta('nota_credito_num'),
        'cliente_fica_produtos'  => $meta('cliente_fica_produtos') === '1',
        'fatura_num'             => $meta('fatura_num'),
        'produto_falta_confirmado' => $meta('produto_falta_confirmado') === '1',
        'pedido_envio'           => $meta('pedido_envio') === '1',
        'vale_codigo'            => $meta('vale_codigo'),
        'edit_url'               => get_edit_post_link($post_id, 'raw') ?: '',
    ];

    $row['b2b_scenario'] = devolucoes_mentor_b2b_detect_scenario($row) ?? '';

    return $row;
}

function devolucoes_mentor_dashboard_table_col_count(string $canal): int
{
    return count(devolucoes_mentor_dashboard_columns($canal));
}

/**
 * Definição única das colunas da tabela (PHP + JS).
 */
function devolucoes_mentor_dashboard_columns(string $canal, string $view_slug = ''): array
{
    $columns = null;

    if ($view_slug !== '') {
        $view = devolucoes_mentor_get_dashboard_view_by_slug($view_slug);
        if ($view) {
            $columns = devolucoes_mentor_dashboard_columns_for_view($view);
        }
    }

    if ($columns === null) {
        $columns = $canal === 'b2b'
            ? devolucoes_mentor_b2b_dashboard_columns_for_scenario('b2b_avaria')
            : devolucoes_mentor_b2c_dashboard_columns_for_tipo('direito_livre_resolucao');
    }

    if (devolucoes_mentor_can_delete_pedidos($canal)) {
        $columns[] = [
            'key'   => 'acoes',
            'label' => 'Ações',
            'type'  => 'actions',
        ];
    }

    return $columns;
}

function devolucoes_mentor_dashboard_row_value(array $row, array $column): string
{
    $key = $column['key'];
    $value = $row[$key] ?? '';

    if ($value === '' && !empty($column['fallback'])) {
        $value = $row[$column['fallback']] ?? '';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_array($value)) {
        return '';
    }

    return (string) $value;
}

function devolucoes_mentor_dashboard_format_date(string $value): string
{
    if ($value === '') {
        return '—';
    }

    $date = substr($value, 0, 10);
    $parts = explode('-', $date);

    if (count($parts) !== 3) {
        return $value;
    }

    return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
}

function devolucoes_mentor_dashboard_render_cell(array $row, array $column, array $tipos, array $estados, array $equipe): void
{
    if (($column['type'] ?? '') !== 'actions' && ($row['canal'] ?? '') === 'b2b' && !devolucoes_mentor_b2b_column_applies($column, $row)) {
        echo '<td><span class="dm-muted">—</span></td>';
        return;
    }

    $type = $column['type'];
    $value = devolucoes_mentor_dashboard_row_value($row, $column);
    $field = $column['field'] ?? $column['key'];
    $td_class = $type === 'actions' ? ' class="dm-cell-actions"' : '';

    echo '<td' . $td_class . '>';

    switch ($type) {
        case 'id':
            echo '<span class="dm-id">' . esc_html($value !== '' ? $value : '—') . '</span>';
            break;

        case 'date':
            echo esc_html(devolucoes_mentor_dashboard_format_date($value));
            break;

        case 'readonly':
            if ($value === '') {
                echo '<span class="dm-muted">—</span>';
            } else {
                echo '<span class="dm-readonly">' . esc_html($value) . '</span>';
            }
            break;

        case 'readonly_subtipo':
            $subtipo_key = devolucoes_mentor_row_subtipo_key($row);
            $labels = devolucoes_mentor_subtipos_erro_labels();
            $display = $labels[$subtipo_key] ?? ($row['subtipo'] ?? '');
            if ($display === '' && !empty($row['tipo_pedido_form'])) {
                if (preg_match('/\s*[—–\-]\s*(.+)$/u', (string) $row['tipo_pedido_form'], $matches)) {
                    $display = trim($matches[1]);
                }
            }
            if ($display === '') {
                echo '<span class="dm-muted">—</span>';
            } else {
                echo '<span class="dm-readonly">' . esc_html($display) . '</span>';
            }
            break;

        case 'readonly_multiline':
            if ($value === '') {
                echo '<span class="dm-muted">—</span>';
            } else {
                echo '<div class="dm-readonly dm-readonly--multiline">' . esc_html($value) . '</div>';
            }
            break;

        case 'files':
            $urls = $row[$column['key']] ?? [];
            if (!is_array($urls) || $urls === []) {
                echo '<span class="dm-muted">—</span>';
                break;
            }
            $prefix = $column['files_prefix'] ?? 'Ficheiro';
            echo '<div class="dm-file-links">';
            foreach ($urls as $index => $url) {
                if (!is_string($url) || $url === '') {
                    continue;
                }
                $label = $prefix . ' ' . ((int) $index + 1);
                echo '<a class="dm-file-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
            }
            echo '</div>';
            break;

        case 'textarea':
            echo '<textarea class="dm-cell-textarea" data-field="' . esc_attr($field) . '" rows="2">' . esc_textarea($value) . '</textarea>';
            break;

        case 'text':
            $placeholder = !empty($column['placeholder']) ? ' placeholder="' . esc_attr($column['placeholder']) . '"' : '';
            echo '<input class="dm-cell-input" data-field="' . esc_attr($field) . '" type="text" value="' . esc_attr($value) . '"' . $placeholder . '>';
            break;

        case 'select_tipo':
            echo '<select class="dm-cell-select" data-field="' . esc_attr($field) . '">';
            echo '<option value="">Selecionar…</option>';
            foreach ($tipos as $key => $label) {
                echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            break;

        case 'select_estado':
            echo '<select class="dm-cell-select" data-field="' . esc_attr($field) . '">';
            echo '<option value="">Selecionar…</option>';
            foreach ($estados as $key => $label) {
                echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            break;

        case 'select_user':
            echo '<select class="dm-cell-select" data-field="' . esc_attr($field) . '">';
            echo '<option value="">Selecionar…</option>';
            foreach ($equipe as $key => $label) {
                echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
            break;

        case 'checkbox':
            $checked = ($row[$column['key']] ?? false) ? ' checked' : '';
            echo '<input class="dm-check" data-field="' . esc_attr($field) . '" type="checkbox"' . $checked . '>';
            break;

        case 'email_button':
            $email_key = $column['email_key'] ?? $field;
            $sent_key = $column['sent_key'] ?? ($email_key . '_enviado');
            $sent = (bool) ($row[$sent_key] ?? false);
            $applies = devolucoes_mentor_email_applies_to_row($row, $email_key);
            if ($sent) {
                echo '<span class="dm-email-sent" title="Email já enviado">Enviado</span>';
            } elseif (!$applies) {
                echo '<span class="dm-muted">—</span>';
            } else {
                $disabled = devolucoes_mentor_equipe_email_sending_disabled();
                $disabled_attr = $disabled
                    ? ' disabled title="Envio de email desativado para a equipa"'
                    : '';
                echo '<button type="button" class="dm-btn-email" data-email-key="' . esc_attr($email_key) . '"' . $disabled_attr . '>Enviar</button>';
            }
            break;

        case 'date_input':
            $date_value = $value !== '' ? substr($value, 0, 10) : '';
            echo '<input class="dm-cell-date" data-field="' . esc_attr($field) . '" type="date" value="' . esc_attr($date_value) . '">';
            break;

        case 'days':
            echo '<span class="dm-days">' . esc_html((string) ($row['dias_em_processo'] ?? 0)) . '</span>';
            break;

        case 'actions':
            echo '<button type="button" class="dm-btn-delete" data-delete-pedido data-pedido-id="' . esc_attr((string) ($row['pedido_id'] ?? '')) . '">Excluir</button>';
            break;

        default:
            echo '<span class="dm-muted">—</span>';
    }

    echo '</td>';
}

function devolucoes_mentor_dashboard_render_table_row(array $row, string $canal, array $tipos, array $estados, array $equipe, string $view_slug = ''): void
{
    $columns = devolucoes_mentor_dashboard_columns($canal, $view_slug);

    echo '<tr data-post-id="' . esc_attr((string) $row['post_id']) . '">';
    foreach ($columns as $column) {
        devolucoes_mentor_dashboard_render_cell($row, $column, $tipos, $estados, $equipe);
    }
    echo '</tr>';
}
