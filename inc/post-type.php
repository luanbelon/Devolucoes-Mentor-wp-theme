<?php
/**
 * Custom Post Types — Pedidos de Devolução B2B e B2C
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_cpt_capabilities(string $canal): array
{
    if ($canal === 'b2c') {
        return [
            'edit_post'          => 'edit_pedido_devolucao_b2c',
            'read_post'          => 'read_pedido_devolucao_b2c',
            'delete_post'        => 'delete_pedido_devolucao_b2c',
            'edit_posts'         => 'edit_pedidos_devolucao_b2c',
            'edit_others_posts'  => 'edit_others_pedidos_devolucao_b2c',
            'publish_posts'      => 'publish_pedidos_devolucao_b2c',
            'read_private_posts' => 'read_private_pedidos_devolucao_b2c',
            'create_posts'       => 'do_not_allow',
        ];
    }

    return [
        'edit_post'          => 'edit_pedido_devolucao',
        'read_post'          => 'read_pedido_devolucao',
        'delete_post'        => 'delete_pedido_devolucao',
        'edit_posts'         => 'edit_pedidos_devolucao',
        'edit_others_posts'  => 'edit_others_pedidos_devolucao',
        'publish_posts'      => 'publish_pedidos_devolucao',
        'read_private_posts' => 'read_private_pedidos_devolucao',
        'create_posts'       => 'do_not_allow',
    ];
}

function devolucoes_mentor_register_post_types(): void
{
    foreach (devolucoes_mentor_canais() as $canal => $config) {
        $singular = 'Pedido ' . $config['label'];

        register_post_type($config['post_type'], [
            'labels' => [
                'name'               => $config['menu_name'],
                'singular_name'      => $singular,
                'menu_name'          => $config['menu_name'],
                'all_items'          => 'Dashboard',
                'view_item'          => 'Ver Pedido',
                'add_new_item'       => 'Adicionar Pedido',
                'add_new'            => 'Adicionar',
                'edit_item'          => 'Editar Pedido',
                'search_items'       => 'Pesquisar Pedidos',
                'not_found'          => 'Nenhum pedido encontrado',
                'not_found_in_trash' => 'Nenhum pedido no lixo',
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'menu_position'       => $canal === 'b2b' ? 26 : 27,
            'menu_icon'           => 'dashicons-undo',
            'capability_type'     => $canal === 'b2b'
                ? ['pedido_devolucao', 'pedidos_devolucao']
                : ['pedido_devolucao_b2c', 'pedidos_devolucao_b2c'],
            'map_meta_cap'        => true,
            'capabilities'        => devolucoes_mentor_cpt_capabilities($canal),
            'hierarchical'        => false,
            'supports'            => ['title'],
            'has_archive'         => false,
            'rewrite'             => false,
            'show_in_rest'        => false,
        ]);
    }
}
add_action('init', 'devolucoes_mentor_register_post_types');

function devolucoes_mentor_register_meta(): void
{
    $fields = [
        'canal', 'empresa', 'nif', 'contacto', 'email', 'telefone',
        'marca', 'tipo_pedido', 'tipo_pedido_key', 'subtipo', 'referencia',
        'quantidade', 'descricao', 'ficheiros', 'fotografias',
        'videos', 'documentos', 'data_submissao',
        'pedido_id', 'motivo', 'sku', 'num_encomenda',
        'nome_cliente', 'email_cliente', 'tipo_classificacao',
        'estado', 'responsavel', 'valor', 'data_finalizacao',
        'email_recebimento_enviado', 'email_1_enviado', 'data_rececao_smartlo', 'email_2_enviado',
        'data_reembolso', 'nota_credito_emitida', 'email_3_enviado',
        'email_4_enviado', 'email_5_enviado', 'email_6_enviado',
        'num_recolha', 'observacoes', 'vale_emitido', 'nota_credito_num',
        'cliente_fica_produtos', 'fatura_num', 'produto_falta_confirmado',
        'pedido_envio', 'vale_codigo', 'b2b_scenario', 'b2b_categoria',
    ];

    foreach (devolucoes_mentor_post_types() as $post_type) {
        foreach ($fields as $field) {
            register_post_meta($post_type, '_dm_' . $field, [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback'     => function () {
                    return devolucoes_mentor_can_manage_pedidos();
                },
            ]);
        }
    }
}
add_action('init', 'devolucoes_mentor_register_meta');

function devolucoes_mentor_add_meta_boxes(): void
{
    foreach (devolucoes_mentor_post_types() as $post_type) {
        add_meta_box(
            'dm-detalhes',
            'Detalhes do Pedido',
            'devolucoes_mentor_render_meta_box',
            $post_type,
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'devolucoes_mentor_add_meta_boxes');

function devolucoes_mentor_render_meta_box(\WP_Post $post): void
{
    $row = devolucoes_mentor_get_pedido_row($post->ID);
    $canal = devolucoes_mentor_canal_for_post_type($post->post_type);
    $config = devolucoes_mentor_canal_config($canal);
    $tipos = devolucoes_mentor_tipos_pedido_admin();
    $estados = devolucoes_mentor_estados();
    $equipe = devolucoes_mentor_equipe_users();

    $fields = [
        'pedido_id'        => 'ID',
        'data_pedido'      => 'Data do Pedido',
        'motivo'           => 'Motivo',
        'marca'            => 'Marca',
        'sku'              => 'SKU',
        'num_encomenda'    => 'Nº da Encomenda',
        'nome_cliente'     => 'Nome do Cliente',
        'email_cliente'    => 'Email do Cliente',
        'valor'            => 'Valor',
        'dias_em_processo' => 'Dias em Processo',
        'num_recolha'      => 'Nº Recolha',
        'observacoes'      => 'Observações',
    ];

    if ($canal === 'b2b') {
        array_splice($fields, 6, 0, [
            'empresa_display' => 'Empresa',
            'nif_display'     => 'NIF',
        ]);
    }

    echo '<table class="form-table"><tbody>';

    foreach ($fields as $key => $label) {
        if ($key === 'empresa_display') {
            $value = esc_html(get_post_meta($post->ID, '_dm_empresa', true));
        } elseif ($key === 'nif_display') {
            $value = esc_html(get_post_meta($post->ID, '_dm_nif', true));
        } else {
            $value = $row[$key] ?? '';
            if ($key === 'observacoes' || $key === 'motivo') {
                $value = nl2br(esc_html((string) $value));
            } else {
                $value = esc_html((string) $value);
            }
        }
        echo '<tr><th>' . esc_html($label) . '</th><td>' . $value . '</td></tr>';
    }

    echo '<tr><th>Tipo de Pedido</th><td>' . esc_html($tipos[$row['tipo_pedido']] ?? '—') . '</td></tr>';
    echo '<tr><th>Estado</th><td>' . esc_html($estados[$row['estado']] ?? '—') . '</td></tr>';
    echo '<tr><th>Responsável</th><td>' . esc_html($equipe[$row['responsavel']] ?? '—') . '</td></tr>';

    $file_groups = [
        'fotografias' => 'Fotografias',
        'videos'      => 'Vídeos',
        'documentos'  => 'Documentos',
    ];

    foreach ($file_groups as $key => $label) {
        $urls = json_decode(get_post_meta($post->ID, '_dm_' . $key, true) ?: '[]', true);
        echo '<tr><th>' . esc_html($label) . '</th><td>';
        if (!empty($urls) && is_array($urls)) {
            echo '<ul style="margin:0">';
            foreach ($urls as $url) {
                echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html(basename($url)) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '—';
        }
        echo '</td></tr>';
    }

    echo '</tbody></table>';
    echo '<p><a class="button button-primary" href="' . esc_url(devolucoes_mentor_admin_menu_url($canal)) . '">Abrir Dashboard</a></p>';
}

/**
 * Save submission as CPT post
 */
function devolucoes_mentor_save_submission(array $data, array $file_urls, string $canal = 'b2b', string $tipo_pedido_key = ''): int|\WP_Error
{
    $tipo = $data['tipo_pedido'];
    if ($data['subtipo']) {
        $tipo .= ' — ' . $data['subtipo'];
    }

    if ($tipo_pedido_key === '') {
        $tipo_pedido_key = (string) ($data['tipo_pedido_key'] ?? '');
    }

    $subtipo_label = $data['subtipo'];
    $subtipo_key = devolucoes_mentor_normalize_subtipo_key($subtipo_label);
    if ($subtipo_key !== '') {
        $labels = devolucoes_mentor_subtipos_erro_labels();
        $subtipo_label = $labels[$subtipo_key] ?? $subtipo_label;
    }

    $b2b_scenario = '';
    if ($canal === 'b2b') {
        $preview_row = [
            'canal'           => 'b2b',
            'tipo_pedido_key' => $tipo_pedido_key,
            'tipo_pedido_form'=> $tipo,
            'subtipo'         => $subtipo_label,
        ];
        $b2b_scenario = devolucoes_mentor_b2b_detect_scenario($preview_row) ?? '';
    }

    $pedido_id = devolucoes_mentor_generate_pedido_id($data['marca']);
    $post_type = devolucoes_mentor_post_type_for_canal($canal);

    $titulo_parte = $canal === 'b2b'
        ? ($data['empresa'] ?: $data['contacto'])
        : ($data['contacto'] ?: $data['nome_cliente']);

    $title = sprintf(
        '%s — %s (%s)',
        $pedido_id,
        $data['referencia'],
        $titulo_parte
    );

    $post_id = wp_insert_post([
        'post_type'   => $post_type,
        'post_title'  => $title,
        'post_status' => 'private',
    ], true);

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    $meta = [
        'canal'              => $canal,
        'empresa'            => $data['empresa'] ?? '',
        'nif'                => $data['nif'] ?? '',
        'contacto'           => $data['contacto'],
        'email'              => $data['email'],
        'telefone'           => $data['telefone'],
        'marca'              => $data['marca'],
        'tipo_pedido'        => $tipo,
        'tipo_pedido_key'    => $tipo_pedido_key,
        'subtipo'            => $subtipo_label,
        'referencia'         => $data['referencia'],
        'quantidade'         => (string) $data['quantidade'],
        'descricao'          => $data['descricao'],
        'data_submissao'     => $data['data'],
        'ficheiros'          => implode(', ', $data['ficheiros']),
        'fotografias'        => wp_json_encode($file_urls['fotografias'] ?? []),
        'videos'             => wp_json_encode($file_urls['videos'] ?? []),
        'documentos'         => wp_json_encode($file_urls['documentos'] ?? []),
        'pedido_id'          => $pedido_id,
        'motivo'             => devolucoes_mentor_map_form_tipo_to_motivo($data['tipo_pedido'], $data['subtipo']),
        'sku'                => $data['referencia'],
        'num_encomenda'      => $data['num_encomenda'] ?? '',
        'nome_cliente'       => $data['nome_cliente'] ?? $data['contacto'],
        'email_cliente'      => $data['email'],
        'tipo_classificacao' => '',
        'estado'             => 'pedido_recebido',
        'responsavel'        => '',
        'valor'              => '',
        'data_finalizacao'   => '',
        'email_recebimento_enviado' => '0',
        'email_1_enviado'    => '0',
        'data_rececao_smartlo' => '',
        'email_2_enviado'    => '0',
        'data_reembolso'     => '',
        'nota_credito_emitida' => '0',
        'email_3_enviado'    => '0',
        'email_4_enviado'    => '0',
        'email_5_enviado'    => '0',
        'email_6_enviado'    => '0',
        'num_recolha'        => '',
        'observacoes'        => $data['descricao'],
        'vale_emitido'       => '0',
        'nota_credito_num'   => '',
        'cliente_fica_produtos' => '0',
        'fatura_num'         => '',
        'produto_falta_confirmado' => '0',
        'pedido_envio'       => '0',
        'vale_codigo'        => '',
        'b2b_scenario'       => $b2b_scenario,
    ];

    foreach ($meta as $key => $value) {
        update_post_meta($post_id, '_dm_' . $key, $value);
    }

    if ($canal === 'b2b') {
        devolucoes_mentor_b2b_send_recebimento_email((int) $post_id);
    }

    return $post_id;
}
