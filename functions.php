<?php

/**

 * Devoluções Mentor — Theme functions

 */



if (!defined('ABSPATH')) {

    exit;

}



define('DEVOLUCOES_MENTOR_VERSION', '1.4.1');

define('DEVOLUCOES_MENTOR_DIR', get_template_directory());

define('DEVOLUCOES_MENTOR_URI', get_template_directory_uri());



require_once DEVOLUCOES_MENTOR_DIR . '/inc/schema.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/b2b.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/b2c-dashboard.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/dashboard-views.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/roles.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/settings.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/post-type.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/routes.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/admin-dashboard.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/email-template.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/emails.php';

require_once DEVOLUCOES_MENTOR_DIR . '/inc/b2b-emails.php';



/**

 * Theme setup

 */

function devolucoes_mentor_setup(): void

{

    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    add_theme_support('custom-logo', [

        'height'      => 48,

        'width'       => 180,

        'flex-height' => true,

        'flex-width'  => true,

    ]);

    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

}

add_action('after_setup_theme', 'devolucoes_mentor_setup');



/**

 * Enqueue assets

 */

function devolucoes_mentor_enqueue_assets(): void

{

    if (!is_page(['b2b', 'b2c'])) {

        return;

    }



    wp_enqueue_style(

        'devolucoes-mentor-fonts',

        'https://fonts.googleapis.com/css2?family=Barlow:wght@300;400;500;600;700&display=swap',

        [],

        null

    );



    wp_enqueue_style(

        'devolucoes-mentor-main',

        DEVOLUCOES_MENTOR_URI . '/assets/css/main.css',

        ['devolucoes-mentor-fonts'],

        DEVOLUCOES_MENTOR_VERSION

    );



    wp_enqueue_script(

        'devolucoes-mentor-form',

        DEVOLUCOES_MENTOR_URI . '/assets/js/form.js',

        [],

        DEVOLUCOES_MENTOR_VERSION,

        true

    );



    wp_localize_script('devolucoes-mentor-form', 'devolucoesMentor', [

        'ajaxUrl' => admin_url('admin-ajax.php'),

        'nonce'   => wp_create_nonce('devolucoes_mentor_submit'),

    ]);

}

add_action('wp_enqueue_scripts', 'devolucoes_mentor_enqueue_assets');



/**

 * Critical header styles — garante header azul mesmo com cache

 */

function devolucoes_mentor_critical_header_css(): void

{

    if (!is_page(['b2b', 'b2c'])) {

        return;

    }



    echo '<style id="devolucoes-mentor-header">'

        . 'header.site-header,.site-header{background-color:#04445f!important;background:#04445f!important;border-bottom:1px solid rgba(255,255,255,.08)!important}'

        . '.site-header .custom-logo-link,.site-header .site-logo{display:inline-flex}'

        . '.site-header .custom-logo,.site-header .site-logo__img{height:48px;width:auto}'

        . '</style>';

}

add_action('wp_head', 'devolucoes_mentor_critical_header_css', 99);



/**

 * Handle form submission via AJAX

 */

function devolucoes_mentor_handle_submit(): void

{

    check_ajax_referer('devolucoes_mentor_submit', 'nonce');



    $canal = devolucoes_mentor_get_canal_from_request();

    if (!in_array($canal, ['b2b', 'b2c'], true)) {

        wp_send_json_error(['message' => 'Canal inválido.']);

    }



    $common_required = [

        'email', 'telefone', 'marca', 'tipo_pedido', 'referencia', 'quantidade', 'descricao',

    ];



    if ($canal === 'b2b') {

        $required = array_merge(['empresa', 'nif', 'contacto'], $common_required);

    } else {

        $required = array_merge(['nome'], $common_required);

    }



    foreach ($required as $field) {

        if (empty($_POST[$field])) {

            wp_send_json_error(['message' => 'Por favor, preencha todos os campos obrigatórios.']);

        }

    }



    if (empty($_POST['confirmacao_verdade']) || empty($_POST['confirmacao_integridade'])) {

        wp_send_json_error(['message' => 'Deve confirmar as declarações antes de submeter.']);

    }



    $tipo_pedido_key = sanitize_text_field($_POST['tipo_pedido']);

    $tipos_validos = devolucoes_mentor_tipos_pedido_form($canal);



    if (!isset($tipos_validos[$tipo_pedido_key])) {

        wp_send_json_error(['message' => 'Tipo de pedido inválido.']);

    }



    if ($tipo_pedido_key === 'erro_encomenda' && empty($_POST['subtipo'])) {

        wp_send_json_error(['message' => 'Selecione o detalhe do erro na encomenda.']);

    }

    if ($canal === 'b2b' && in_array($tipo_pedido_key, ['erro_encomenda', 'avaria_cliente'], true) && empty($_POST['num_encomenda'])) {

        wp_send_json_error(['message' => 'Indique o número da encomenda.']);

    }



    if ($canal === 'b2b') {

        $nif = sanitize_text_field($_POST['nif']);

        if (!preg_match('/^\d{9}$/', $nif)) {

            wp_send_json_error(['message' => 'O NIF deve ter 9 dígitos.']);

        }

    } else {

        $nif = sanitize_text_field($_POST['nif'] ?? '');

        if ($nif !== '' && !preg_match('/^\d{9}$/', $nif)) {

            wp_send_json_error(['message' => 'O NIF deve ter 9 dígitos.']);

        }

    }



    $foto_count = 0;

    if (!empty($_FILES['fotografias']['name'][0])) {

        $foto_count = count(array_filter($_FILES['fotografias']['name']));

        if ($foto_count > 10) {

            wp_send_json_error(['message' => 'Máximo de 10 fotografias permitido.']);

        }

    }



    $video_count = 0;

    if (!empty($_FILES['videos']['name'][0])) {

        $video_count = count(array_filter($_FILES['videos']['name']));

        if ($video_count > 3) {

            wp_send_json_error(['message' => 'Máximo de 3 vídeos permitido.']);

        }

    }



    $upload_dir = wp_upload_dir();

    $submission_dir = $upload_dir['basedir'] . '/devolucoes/' . date('Y-m-d') . '_' . wp_generate_password(8, false);

    wp_mkdir_p($submission_dir);



    $file_urls = [

        'fotografias' => devolucoes_mentor_save_uploads('fotografias', $submission_dir, $upload_dir['baseurl'] . '/devolucoes/' . basename($submission_dir), ['image/jpeg', 'image/png', 'image/webp', 'image/heic']),

        'videos'      => devolucoes_mentor_save_uploads('videos', $submission_dir, $upload_dir['baseurl'] . '/devolucoes/' . basename($submission_dir), ['video/mp4', 'video/quicktime', 'video/webm']),

        'documentos'  => devolucoes_mentor_save_uploads('documentos', $submission_dir, $upload_dir['baseurl'] . '/devolucoes/' . basename($submission_dir), ['application/pdf', 'image/jpeg', 'image/png']),

    ];



    $saved_files = array_merge($file_urls['fotografias'], $file_urls['videos'], $file_urls['documentos']);



    if ($canal === 'b2b') {

        $data = [

            'empresa'       => sanitize_text_field($_POST['empresa']),

            'nif'           => $nif,

            'contacto'      => sanitize_text_field($_POST['contacto']),

            'email'         => sanitize_email($_POST['email']),

            'telefone'      => sanitize_text_field($_POST['telefone']),

            'nome_cliente'  => sanitize_text_field($_POST['contacto']),

            'num_encomenda' => sanitize_text_field($_POST['num_encomenda'] ?? ''),

        ];

    } else {

        $nome = sanitize_text_field($_POST['nome']);

        $data = [

            'empresa'       => '',

            'nif'           => $nif,

            'contacto'      => $nome,

            'email'         => sanitize_email($_POST['email']),

            'telefone'      => sanitize_text_field($_POST['telefone']),

            'nome_cliente'  => $nome,

            'num_encomenda' => sanitize_text_field($_POST['num_encomenda'] ?? ''),

        ];

    }



    $data = array_merge($data, [

        'marca'       => sanitize_text_field($_POST['marca']),

        'tipo_pedido' => $tipos_validos[$tipo_pedido_key],

        'tipo_pedido_key' => $tipo_pedido_key,

        'subtipo'     => $tipo_pedido_key === 'erro_encomenda'

            ? sanitize_text_field($_POST['subtipo'] ?? '')

            : '',

        'referencia'  => sanitize_text_field($_POST['referencia']),

        'quantidade'  => absint($_POST['quantidade']),

        'descricao'   => sanitize_textarea_field($_POST['descricao']),

        'ficheiros'   => array_map('basename', $saved_files),

        'data'        => current_time('mysql'),

        'canal'       => $canal,

    ]);



    $post_id = devolucoes_mentor_save_submission($data, $file_urls, $canal);



    if (is_wp_error($post_id)) {

        wp_send_json_error(['message' => 'Erro ao guardar o pedido. Tente novamente.']);

    }



    $pedido_id = get_post_meta($post_id, '_dm_pedido_id', true);

    $data['pedido_id'] = $pedido_id;



    $admin_email = get_option('admin_email');

    $canal_label = devolucoes_mentor_canal_nome($canal);

    $subject = sprintf('[%s] %s — %s', $canal_label, $pedido_id ?: $data['marca'], $data['referencia']);

    $body = devolucoes_mentor_prepare_email_body(devolucoes_mentor_format_email($data, $canal));

    $headers = ['Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $data['email']];

    wp_mail($admin_email, $subject, $body, $headers);



    wp_send_json_success(['message' => 'Pedido submetido com sucesso! Entraremos em contacto em breve.']);

}

add_action('wp_ajax_devolucoes_submit', 'devolucoes_mentor_handle_submit');

add_action('wp_ajax_nopriv_devolucoes_submit', 'devolucoes_mentor_handle_submit');



/**

 * Save uploaded files to directory and return public URLs

 */

function devolucoes_mentor_save_uploads(string $field, string $dir, string $base_url, array $allowed_types): array

{

    $saved = [];



    if (empty($_FILES[$field]['name'][0])) {

        return $saved;

    }



    $files = $_FILES[$field];

    $count = count($files['name']);



    for ($i = 0; $i < $count; $i++) {

        if (empty($files['name'][$i])) {

            continue;

        }



        $file = [

            'name'     => $files['name'][$i],

            'type'     => $files['type'][$i],

            'tmp_name' => $files['tmp_name'][$i],

            'error'    => $files['error'][$i],

            'size'     => $files['size'][$i],

        ];



        if ($file['error'] !== UPLOAD_ERR_OK) {

            continue;

        }



        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mime = finfo_file($finfo, $file['tmp_name']);

        finfo_close($finfo);



        if (!in_array($mime, $allowed_types, true)) {

            continue;

        }



        $filename = sanitize_file_name($file['name']);

        $dest = $dir . '/' . $filename;



        if (move_uploaded_file($file['tmp_name'], $dest)) {

            $saved[] = $base_url . '/' . $filename;

        }

    }



    return $saved;

}



/**

 * Format submission email

 */

function devolucoes_mentor_format_email(array $data, string $canal = 'b2b'): string

{

    $tipo = $data['tipo_pedido'];

    if ($data['subtipo']) {

        $tipo .= ' — ' . $data['subtipo'];

    }



    if ($canal === 'b2c') {

        return sprintf(

            '<h2>Novo Pedido de Apoio Cliente</h2>

            <p><strong>ID:</strong> %s</p>

            <p><strong>Nome:</strong> %s</p>

            <p><strong>Email:</strong> %s</p>

            <p><strong>Telefone:</strong> %s</p>

            <p><strong>NIF:</strong> %s</p>

            <p><strong>Nº Encomenda:</strong> %s</p>

            <hr>

            <p><strong>Marca:</strong> %s</p>

            <p><strong>Tipo de Pedido:</strong> %s</p>

            <p><strong>Referência:</strong> %s</p>

            <p><strong>Quantidade:</strong> %d</p>

            <p><strong>Descrição:</strong><br>%s</p>

            <hr>

            <p><strong>Ficheiros:</strong> %s</p>

            <p><em>Submetido em %s</em></p>',

            esc_html($data['pedido_id'] ?? '—'),

            esc_html($data['nome_cliente'] ?? $data['contacto']),

            esc_html($data['email']),

            esc_html($data['telefone']),

            esc_html($data['nif'] ?: '—'),

            esc_html($data['num_encomenda'] ?: '—'),

            esc_html($data['marca']),

            esc_html($tipo),

            esc_html($data['referencia']),

            $data['quantidade'],

            nl2br(esc_html($data['descricao'])),

            esc_html(implode(', ', $data['ficheiros'])),

            esc_html($data['data'])

        );

    }



    return sprintf(

        '<h2>Novo Pedido de Pós Venda</h2>

        <p><strong>ID:</strong> %s</p>

        <p><strong>Empresa:</strong> %s</p>

        <p><strong>NIF:</strong> %s</p>

        <p><strong>Contacto:</strong> %s</p>

        <p><strong>Email:</strong> %s</p>

        <p><strong>Telefone:</strong> %s</p>

        <hr>

        <p><strong>Marca:</strong> %s</p>

        <p><strong>Tipo de Pedido:</strong> %s</p>

        <p><strong>Referência:</strong> %s</p>

        <p><strong>Quantidade:</strong> %d</p>

        <p><strong>Descrição:</strong><br>%s</p>

        <hr>

        <p><strong>Ficheiros:</strong> %s</p>

        <p><em>Submetido em %s</em></p>',

        esc_html($data['pedido_id'] ?? '—'),

        esc_html($data['empresa']),

        esc_html($data['nif']),

        esc_html($data['contacto']),

        esc_html($data['email']),

        esc_html($data['telefone']),

        esc_html($data['marca']),

        esc_html($tipo),

        esc_html($data['referencia']),

        $data['quantidade'],

        nl2br(esc_html($data['descricao'])),

        esc_html(implode(', ', $data['ficheiros'])),

        esc_html($data['data'])

    );

}


