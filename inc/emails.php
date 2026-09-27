<?php
/**
 * Templates de email, admin e envio
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_email_template_groups(): array
{
    return [
        'devolucao_14_dias' => [
            'label'   => 'Devolução ao abrigo do direito de livre resolução (14 dias)',
            'emails'  => ['email_1', 'email_2', 'email_3'],
            'tipos'   => ['direito_livre_resolucao', 'pedido_fora_prazo'],
        ],
        'troca_comercial' => [
            'label'   => 'Trocas comerciais',
            'emails'  => ['email_1', 'email_2'],
            'tipos'   => ['troca_comercial'],
        ],
        'defeito_expedicao' => [
            'label'   => 'Produtos com defeito ou erro de expedição',
            'emails'  => ['email_1', 'email_2', 'email_3'],
            'tipos'   => ['produto_defeituoso', 'erro_expedicao'],
        ],
    ];
}

function devolucoes_mentor_email_default_templates(): array
{
    return [
        'devolucao_14_dias' => [
            'email_1' => [
                'subject' => 'Recebemos o seu pedido de devolução da encomenda [Número]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pelo seu contacto.</p>
<p>Confirmamos a receção do seu pedido de devolução referente à encomenda <strong>[Número]</strong>.</p>
<p>Dispõe agora de 14 dias para nos enviar os artigos para a seguinte morada:</p>
<p><strong>[Morada]</strong></p>
<p>No interior da encomenda deve indicar o nº da sua encomenda.</p>
<p>Assim que os produtos forem recebidos e verificados pela nossa equipa, procederemos ao respetivo reembolso de acordo com os nossos Termos e Condições.</p>
<p>Com os melhores cumprimentos,<br><strong>[Marca]</strong></p>',
            ],
            'email_2' => [
                'subject' => 'Reembolso aprovado encomenda [Número]',
                'body'    => '<p>Olá [Nome],</p>
<p>Confirmamos a receção e validação dos artigos devolvidos referentes à encomenda <strong>[Número]</strong>.</p>
<p>O reembolso será efetuado através do mesmo método de pagamento utilizado na compra.</p>
<p>O processamento poderá demorar alguns dias úteis, dependendo da entidade bancária.</p>
<p>Obrigado.</p>
<p>Com os melhores cumprimentos,<br><strong>[Marca]</strong></p>',
            ],
            'email_3' => [
                'subject' => 'Pedido de devolução encomenda [Número]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pelo seu contacto.</p>
<p>Verificámos que o prazo legal de 14 dias para exercício do direito de livre resolução já se encontra ultrapassado.</p>
<p>Por esse motivo, não nos é possível aceitar a devolução para reembolso.</p>
<p>Continuamos disponíveis para esclarecer qualquer questão adicional.</p>
<p>Com os melhores cumprimentos,<br><strong>[Marca]</strong></p>',
            ],
        ],
        'troca_comercial' => [
            'email_1' => [
                'subject' => 'Troca aprovada encomenda [Número]',
                'body'    => '<p>Olá [Nome],</p>
<p>Confirmamos que a sua solicitação de troca foi aprovada.</p>
<p>Por favor envie o artigo para:</p>
<p><strong>[Morada]</strong></p>
<p>No interior da encomenda deve indicar o nº da sua encomenda.</p>
<p>Assim que o recebermos e verificarmos o seu estado, procederemos à emissão de vale de compra.</p>
<p>Com os melhores cumprimentos,<br><strong>[Marca]</strong></p>',
            ],
            'email_2' => [
                'subject' => 'Vale de compra encomenda [Número]',
                'body'    => '<p>Olá [Nome],</p>
<p>Confirmamos que recebemos e validámos o artigo devolvido.</p>
<p>Conforme acordado, emitimos um <strong>vale de compra no valor de [XX,XX €]</strong>, que poderá utilizar numa próxima compra na nossa loja online.</p>
<p><strong>Código do vale:</strong> [CÓDIGO]<br>
<strong>Valor:</strong> [XX,XX €]<br>
<strong>Validade:</strong> 1 Ano</p>
<p>Para utilizar o vale, basta introduzir o código acima no checkout, no campo &quot;Código de desconto&quot;.</p>
<p>Caso o valor da nova encomenda seja superior ao valor do vale, apenas terá de pagar a diferença. Se for inferior, o valor remanescente continuará disponível para utilização futura.</p>
<p>Se tiver alguma dúvida ou precisar de ajuda na escolha de um novo artigo, estamos inteiramente disponíveis.</p>
<p>Obrigado pela sua confiança.</p>
<p>Com os melhores cumprimentos,<br>Equipa <strong>[Marca]</strong></p>',
            ],
        ],
        'defeito_expedicao' => [
            'email_1' => [
                'subject' => 'Pedido de informação adicional',
                'body'    => '<p>Olá [Nome],</p>
<p>Lamentamos a situação reportada.</p>
<p>Para que possamos analisar o caso, pedimos por favor o envio de:</p>
<ul>
<li>Fotografias do artigo</li>
<li>Fotografias da embalagem (se aplicável)</li>
<li>Descrição do problema identificado</li>
</ul>
<p>Assim que recebermos a informação, analisaremos o caso com a maior brevidade possível.</p>
<p>Com os melhores cumprimentos,<br><strong>[Marca]</strong></p>',
            ],
            'email_2' => [
                'subject' => 'Recolha do artigo aprovada',
                'body'    => '<p>Olá [Nome],</p>
<p>Após análise da informação enviada, confirmamos que o seu pedido foi aprovado.</p>
<p>Iremos proceder à recolha do artigo, sem qualquer custo para si.</p>
<p>Nos próximos dias será contactado pela transportadora para agendamento da recolha, ou receberá as respetivas instruções de envio, consoante o método aplicável.</p>
<p>Assim que o artigo for recebido nas nossas instalações e a verificação estiver concluída, procederemos ao reembolso do valor pago através do mesmo método de pagamento utilizado na compra.</p>
<p>Agradecemos a sua compreensão e lamentamos o incómodo causado.</p>
<p>Com os melhores cumprimentos,<br>Equipa <strong>[Marca]</strong></p>',
            ],
            'email_3' => [
                'subject' => 'O seu reembolso foi processado',
                'body'    => '<p>Olá [Nome],</p>
<p>Confirmamos a receção e validação do artigo recolhido.</p>
<p>O reembolso da sua encomenda foi agora processado através do mesmo método de pagamento utilizado na compra.</p>
<p>Dependendo da sua entidade bancária, o valor poderá demorar alguns dias úteis a ficar disponível na sua conta.</p>
<p>Lamentamos o incómodo causado e agradecemos a compreensão.</p>
<p>Caso necessite de qualquer esclarecimento adicional, estaremos inteiramente disponíveis.</p>
<p>Com os melhores cumprimentos,<br>Equipa <strong>[Marca]</strong></p>',
            ],
        ],
    ];
}

function devolucoes_mentor_email_default_morada(): string
{
    return 'SmartLo — Morada de devoluções (configurar em Emails)';
}

function devolucoes_mentor_get_email_morada(): string
{
    $morada = get_option('dm_email_morada', '');
    if ($morada === '') {
        return devolucoes_mentor_email_default_morada();
    }

    return $morada;
}

function devolucoes_mentor_get_email_templates(): array
{
    $stored = get_option('dm_email_templates', []);
    $defaults = devolucoes_mentor_email_default_templates();

    if (!is_array($stored)) {
        $stored = [];
    }

    $merged = [];
    foreach ($defaults as $group => $emails) {
        $merged[$group] = [];
        foreach ($emails as $key => $template) {
            $saved = $stored[$group][$key] ?? [];
            $merged[$group][$key] = [
                'subject' => (string) ($saved['subject'] ?? $template['subject']),
                'body'    => (string) ($saved['body'] ?? $template['body']),
            ];
        }
    }

    return $merged;
}

function devolucoes_mentor_tipo_to_email_group(string $tipo): ?string
{
    foreach (devolucoes_mentor_email_template_groups() as $group => $config) {
        if (in_array($tipo, $config['tipos'], true)) {
            return $group;
        }
    }

    return null;
}

function devolucoes_mentor_format_valor_email(string $valor): string
{
    if ($valor === '') {
        return '';
    }

    $normalized = str_replace([' ', '€'], '', $valor);
    $normalized = str_replace(',', '.', $normalized);
    if (is_numeric($normalized)) {
        return number_format((float) $normalized, 2, ',', '.') . ' €';
    }

    return $valor;
}

function devolucoes_mentor_email_placeholders(array $row): array
{
    $numero = $row['num_encomenda'] ?: $row['pedido_id'];
    $vale_codigo = (string) get_post_meta((int) $row['post_id'], '_dm_vale_codigo', true);
    if ($vale_codigo === '') {
        $vale_codigo = (string) ($row['num_recolha'] ?? '');
    }

    return [
        '[Nome]'     => $row['nome_cliente'] ?: $row['contacto'] ?: 'Cliente',
        '[Número]'   => $numero,
        '[Morada]'   => devolucoes_mentor_get_email_morada(),
        '[Marca]'    => $row['marca'] ?: 'Mentor',
        '[XX,XX €]'  => devolucoes_mentor_format_valor_email((string) ($row['valor'] ?? '')),
        '[CÓDIGO]'   => $vale_codigo,
    ];
}

function devolucoes_mentor_apply_email_placeholders(string $text, array $placeholders): string
{
    foreach ($placeholders as $token => $value) {
        $text = str_replace($token, $value, $text);
    }

    return $text;
}

function devolucoes_mentor_get_pedido_email_template(array $row, string $email_key): ?array
{
    if (($row['canal'] ?? '') === 'b2b') {
        return devolucoes_mentor_b2b_get_pedido_email_template($row, $email_key);
    }

    $tipo = (string) ($row['tipo_pedido'] ?? '');
    if ($tipo === '') {
        return null;
    }

    $group = devolucoes_mentor_tipo_to_email_group($tipo);
    if ($group === null) {
        return null;
    }

    $groups = devolucoes_mentor_email_template_groups();
    if (!in_array($email_key, $groups[$group]['emails'], true)) {
        return null;
    }

    $templates = devolucoes_mentor_get_email_templates();
    $template = $templates[$group][$email_key] ?? null;
    if (!$template) {
        return null;
    }

    $placeholders = devolucoes_mentor_email_placeholders($row);

    return [
        'group'      => $group,
        'email_key'  => $email_key,
        'subject'    => devolucoes_mentor_apply_email_placeholders($template['subject'], $placeholders),
        'body'       => devolucoes_mentor_apply_email_placeholders($template['body'], $placeholders),
    ];
}

function devolucoes_mentor_email_meta_key(string $email_key): string
{
    return $email_key . '_enviado';
}

function devolucoes_mentor_can_send_pedido_email(array $row, string $email_key): bool
{
    if (($row['canal'] ?? '') === 'b2b') {
        return devolucoes_mentor_b2b_can_send_email($row, $email_key);
    }

    if ($email_key === 'email_1') {
        return !($row['email_1_enviado'] ?? false);
    }
    if ($email_key === 'email_2') {
        return !($row['email_2_enviado'] ?? false);
    }
    if ($email_key === 'email_3') {
        return !($row['email_3_enviado'] ?? false);
    }

    return false;
}

function devolucoes_mentor_email_applies_to_row(array $row, string $email_key): bool
{
    return devolucoes_mentor_get_pedido_email_template($row, $email_key) !== null;
}

function devolucoes_mentor_send_pedido_email(int $post_id, string $email_key, string $custom_body = ''): array
{
    $row = devolucoes_mentor_get_pedido_row($post_id);

    if (($row['canal'] ?? '') === 'b2b') {
        return devolucoes_mentor_b2b_send_email($post_id, $email_key, $custom_body);
    }

    $template = devolucoes_mentor_get_pedido_email_template($row, $email_key);

    if ($template === null) {
        return ['success' => false, 'message' => 'Template de email não disponível para esta classificação.'];
    }

    if (!devolucoes_mentor_can_send_pedido_email($row, $email_key)) {
        return ['success' => false, 'message' => 'Este email já foi enviado.'];
    }

    $to = $row['email_cliente'];
    if ($to === '' || !is_email($to)) {
        return ['success' => false, 'message' => 'Email do cliente inválido ou em falta.'];
    }

    if ($template['group'] === 'troca_comercial' && $email_key === 'email_2') {
        $codigo = devolucoes_mentor_email_placeholders($row)['[CÓDIGO]'];
        if ($codigo === '') {
            return ['success' => false, 'message' => 'Preencha o Nº Recolha (código do vale) antes de enviar este email.'];
        }
    }

    $body_content = $custom_body !== '' ? $custom_body : $template['body'];
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $sent = wp_mail($to, $template['subject'], devolucoes_mentor_prepare_email_body($body_content), $headers);

    if (!$sent) {
        return ['success' => false, 'message' => 'Falha ao enviar o email. Verifique a configuração de email do WordPress.'];
    }

    $meta_key = devolucoes_mentor_email_meta_key($email_key);
    update_post_meta($post_id, '_dm_' . $meta_key, '1');
    update_post_meta($post_id, '_dm_' . $meta_key . '_data', current_time('mysql'));

    return [
        'success' => true,
        'message' => 'Email enviado com sucesso.',
        'row'     => devolucoes_mentor_get_pedido_row($post_id),
    ];
}

function devolucoes_mentor_email_tipo_map_for_js(): array
{
    $map = [];
    foreach (devolucoes_mentor_email_template_groups() as $group) {
        foreach ($group['tipos'] as $tipo) {
            $map[$tipo] = $group['emails'];
        }
    }

    return $map;
}

function devolucoes_mentor_email_test_row(): array
{
    return [
        'post_id'          => 0,
        'canal'            => 'b2b',
        'nome_cliente'     => 'Cliente Exemplo',
        'contacto'         => 'Cliente Exemplo',
        'num_encomenda'    => 'ENC-2026-12345',
        'pedido_id'        => 'DEV-2026-XX-00001',
        'marca'            => 'Owlet',
        'referencia'       => 'OW_001',
        'sku'              => 'OW_001',
        'quantidade'       => '2',
        'valor'            => '49,90',
        'num_recolha'      => 'VALE-TESTE',
        'nota_credito_num' => 'NC-1234',
        'fatura_num'       => 'FT-5678',
    ];
}

function devolucoes_mentor_email_test_placeholders(): array
{
    return devolucoes_mentor_b2b_email_placeholders(devolucoes_mentor_email_test_row());
}

function devolucoes_mentor_email_test_sample_content(): string
{
    return '<h2>Email de teste</h2>
<p>Olá <strong>Cliente Exemplo</strong>,</p>
<p>Este é um email de teste para visualizar o template da Mentor Company com o header, logo e formatação aplicados.</p>
<p><strong>Encomenda:</strong> ENC-2026-12345<br>
<strong>Produto:</strong> OW_001<br>
<strong>Quantidade:</strong> 2</p>
<ul>
<li>Exemplo de lista com item 1</li>
<li>Exemplo de lista com item 2</li>
</ul>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>';
}

function devolucoes_mentor_send_test_email(string $to, string $subject, string $body_html): array
{
    if (!is_email($to)) {
        return ['success' => false, 'message' => 'Indique um email válido.'];
    }

    if ($body_html === '') {
        return ['success' => false, 'message' => 'O conteúdo do email não pode estar vazio.'];
    }

    $subject = sanitize_text_field($subject);
    if (!str_starts_with($subject, '[TESTE]')) {
        $subject = '[TESTE] ' . $subject;
    }

    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $sent = wp_mail($to, $subject, devolucoes_mentor_prepare_email_body($body_html), $headers);

    if (!$sent) {
        return ['success' => false, 'message' => 'Falha ao enviar. Verifique a configuração de email do WordPress.'];
    }

    return [
        'success' => true,
        'message' => 'Email de teste enviado para ' . $to . '.',
    ];
}

function devolucoes_mentor_ajax_send_test_email(): void
{
    check_ajax_referer('dm_emails_test', 'nonce');

    if (!devolucoes_mentor_can_manage_emails()) {
        wp_send_json_error(['message' => 'Sem permissão.'], 403);
    }

    $to = sanitize_email(wp_unslash($_POST['to'] ?? ''));
    $source = sanitize_key($_POST['source'] ?? 'wrapper');

    if ($source === 'wrapper') {
        $result = devolucoes_mentor_send_test_email(
            $to,
            'Template de email — Mentor Company',
            devolucoes_mentor_email_test_sample_content()
        );
    } elseif ($source === 'custom') {
        $subject = sanitize_text_field(wp_unslash($_POST['subject'] ?? ''));
        $body_raw = wp_kses_post(wp_unslash($_POST['body'] ?? ''));
        $placeholders = devolucoes_mentor_email_test_placeholders();

        $result = devolucoes_mentor_send_test_email(
            $to,
            devolucoes_mentor_apply_email_placeholders($subject, $placeholders),
            devolucoes_mentor_apply_email_placeholders($body_raw, $placeholders)
        );
    } else {
        wp_send_json_error(['message' => 'Tipo de teste inválido.']);
    }

    if (!$result['success']) {
        wp_send_json_error(['message' => $result['message']]);
    }

    wp_send_json_success(['message' => $result['message']]);
}
add_action('wp_ajax_dm_send_test_email', 'devolucoes_mentor_ajax_send_test_email');

function devolucoes_mentor_can_manage_emails(): bool
{
    return current_user_can('manage_options') || current_user_can('dm_manage_emails');
}

function devolucoes_mentor_emails_admin_menu(): void
{
    add_menu_page(
        'Emails',
        'Emails',
        'dm_manage_emails',
        'dm-emails',
        'devolucoes_mentor_render_emails_admin_page',
        'dashicons-email-alt',
        28
    );
}
add_action('admin_menu', 'devolucoes_mentor_emails_admin_menu');

function devolucoes_mentor_grant_email_caps(): void
{
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('dm_manage_emails', true);
    }

    $role = get_role('equipe_mentor');
    if ($role) {
        $role->add_cap('dm_manage_emails', false);
    }
}
add_action('init', 'devolucoes_mentor_grant_email_caps', 6);

function devolucoes_mentor_enqueue_emails_admin_assets(string $hook): void
{
    if ($hook !== 'toplevel_page_dm-emails') {
        return;
    }

    wp_enqueue_editor();

    $admin_css = DEVOLUCOES_MENTOR_DIR . '/assets/css/admin-dashboard.css';
    wp_enqueue_style(
        'devolucoes-mentor-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/css/admin-dashboard.css',
        [],
        file_exists($admin_css) ? (string) filemtime($admin_css) : DEVOLUCOES_MENTOR_VERSION
    );

    $emails_js = DEVOLUCOES_MENTOR_DIR . '/assets/js/emails-admin.js';
    wp_enqueue_script(
        'devolucoes-mentor-emails-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/js/emails-admin.js',
        ['jquery'],
        file_exists($emails_js) ? (string) filemtime($emails_js) : DEVOLUCOES_MENTOR_VERSION,
        true
    );

    $user = wp_get_current_user();
    wp_localize_script('devolucoes-mentor-emails-admin', 'dmEmailsAdmin', [
        'ajaxUrl'      => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('dm_emails_test'),
        'defaultEmail' => $user->user_email ?: get_option('admin_email'),
    ]);
}
add_action('admin_enqueue_scripts', 'devolucoes_mentor_enqueue_emails_admin_assets');

function devolucoes_mentor_render_emails_admin_page(): void
{
    if (!devolucoes_mentor_can_manage_emails()) {
        wp_die('Sem permissão.');
    }

    if (
        isset($_POST['dm_emails_save'])
        && check_admin_referer('dm_emails_save', 'dm_emails_nonce')
    ) {
        $morada = isset($_POST['dm_email_morada'])
            ? sanitize_textarea_field(wp_unslash($_POST['dm_email_morada']))
            : '';
        update_option('dm_email_morada', $morada);

        $groups = devolucoes_mentor_email_template_groups();
        $templates = [];

        foreach ($groups as $group_key => $group) {
            $templates[$group_key] = [];
            foreach ($group['emails'] as $email_key) {
                $subject_field = 'dm_subject_' . $group_key . '_' . $email_key;
                $body_field    = 'dm_body_' . $group_key . '_' . $email_key;

                $subject = isset($_POST[$subject_field])
                    ? sanitize_text_field(wp_unslash($_POST[$subject_field]))
                    : '';
                $body = isset($_POST[$body_field])
                    ? wp_kses_post(wp_unslash($_POST[$body_field]))
                    : '';

                $templates[$group_key][$email_key] = [
                    'subject' => $subject,
                    'body'    => $body,
                ];
            }
        }

        update_option('dm_email_templates', $templates);

        if (isset($_POST['dm_email_smartlo'])) {
            $smartlo = sanitize_email(wp_unslash($_POST['dm_email_smartlo']));
            update_option('dm_email_smartlo', $smartlo);
        }

        $extras_raw = isset($_POST['dm_emails_internos']) ? (array) wp_unslash($_POST['dm_emails_internos']) : [];
        $extras = [];
        foreach ($extras_raw as $extra) {
            $extra = sanitize_email(is_string($extra) ? trim($extra) : '');
            if ($extra !== '' && is_email($extra) && !in_array($extra, $extras, true)) {
                $extras[] = $extra;
            }
        }
        update_option('dm_emails_internos', $extras);

        $b2b_defaults = devolucoes_mentor_b2b_email_default_templates();
        $b2b_templates = [];

        foreach ($b2b_defaults as $scenario_key => $scenario_emails) {
            $b2b_templates[$scenario_key] = [];
            foreach ($scenario_emails as $email_key => $default_template) {
                $subject_field = 'dm_b2b_subject_' . $scenario_key . '_' . $email_key;
                $body_field = 'dm_b2b_body_' . $scenario_key . '_' . $email_key;
                $b2b_templates[$scenario_key][$email_key] = [
                    'subject' => isset($_POST[$subject_field])
                        ? sanitize_text_field(wp_unslash($_POST[$subject_field]))
                        : $default_template['subject'],
                    'body' => isset($_POST[$body_field])
                        ? wp_kses_post(wp_unslash($_POST[$body_field]))
                        : $default_template['body'],
                ];
            }
        }

        update_option('dm_email_templates_b2b', $b2b_templates);
        echo '<div class="notice notice-success is-dismissible"><p>Templates guardados.</p></div>';
    }

    $morada = devolucoes_mentor_get_email_morada();
    $templates = devolucoes_mentor_get_email_templates();
    $groups = devolucoes_mentor_email_template_groups();
    $placeholders_help = '[Nome], [Número], [Morada], [Marca], [XX,XX €], [CÓDIGO]';

    ?>
    <div class="wrap dm-wrap dm-emails-wrap">
        <div class="dm-header">
            <div>
                <h1 class="dm-header__title">Templates de Email</h1>
                <p class="dm-header__sub">Edite os textos dos emails enviados aos clientes. Placeholders: <?php echo esc_html($placeholders_help); ?></p>
            </div>
        </div>

        <div class="dm-emails-card dm-emails-card--test">
            <h2>Email de teste</h2>
            <p class="dm-header__sub">Envie um email de teste para verificar o template visual (header, logo e formatação).</p>
            <div class="dm-emails-test">
                <label for="dm-test-email-to" class="dm-emails-test__label">Enviar para</label>
                <input
                    type="email"
                    id="dm-test-email-to"
                    class="dm-emails-subject dm-emails-test__input"
                    value="<?php echo esc_attr(wp_get_current_user()->user_email ?: get_option('admin_email')); ?>"
                    placeholder="email@exemplo.com"
                >
                <button type="button" class="button button-secondary" id="dm-send-template-test">
                    Enviar teste do template
                </button>
            </div>
            <p id="dm-test-email-feedback" class="dm-emails-test__feedback" hidden></p>
        </div>

        <form method="post" class="dm-emails-form">
            <?php wp_nonce_field('dm_emails_save', 'dm_emails_nonce'); ?>

            <div class="dm-emails-card">
                <h2>Morada de devolução</h2>
                <p class="dm-header__sub">Utilizada no placeholder <code>[Morada]</code> nos emails.</p>
                <textarea name="dm_email_morada" class="dm-emails-morada" rows="3"><?php echo esc_textarea($morada); ?></textarea>
            </div>

            <?php foreach ($groups as $group_key => $group) : ?>
                <div class="dm-emails-card">
                    <h2><?php echo esc_html($group['label']); ?></h2>

                    <?php foreach ($group['emails'] as $index => $email_key) :
                        $num = $index + 1;
                        $template = $templates[$group_key][$email_key];
                        $subject_id = 'dm_subject_' . $group_key . '_' . $email_key;
                        $body_id    = 'dm_body_' . $group_key . '_' . $email_key;
                        ?>
                        <div class="dm-emails-block">
                            <div class="dm-emails-block__head">
                                <h3>Email <?php echo esc_html((string) $num); ?></h3>
                                <button
                                    type="button"
                                    class="button button-secondary dm-btn-test-email"
                                    data-subject-id="<?php echo esc_attr($subject_id); ?>"
                                    data-body-id="<?php echo esc_attr($body_id); ?>"
                                >Enviar teste</button>
                            </div>
                            <p>
                                <label for="<?php echo esc_attr($subject_id); ?>"><strong>Assunto</strong></label>
                                <input
                                    type="text"
                                    class="dm-emails-subject"
                                    id="<?php echo esc_attr($subject_id); ?>"
                                    name="<?php echo esc_attr($subject_id); ?>"
                                    value="<?php echo esc_attr($template['subject']); ?>"
                                >
                            </p>
                            <p>
                                <label for="<?php echo esc_attr($body_id); ?>"><strong>Corpo</strong></label>
                                <?php
                                wp_editor(
                                    $template['body'],
                                    $body_id,
                                    [
                                        'textarea_name' => $body_id,
                                        'textarea_rows' => 12,
                                        'media_buttons' => false,
                                        'teeny'         => false,
                                        'quicktags'     => true,
                                    ]
                                );
                                ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <h2 class="dm-emails-section-title">Emails Pós Venda — Procedimento interno</h2>
            <p class="dm-header__sub">Configure também o email da Smartlo utilizado nos envios internos.</p>
            <div class="dm-emails-card">
                <label for="dm_email_smartlo"><strong>Email Smartlo</strong></label>
                <input
                    type="email"
                    class="dm-emails-subject"
                    id="dm_email_smartlo"
                    name="dm_email_smartlo"
                    value="<?php echo esc_attr(get_option('dm_email_smartlo', '')); ?>"
                    placeholder="<?php echo esc_attr(get_option('admin_email')); ?>"
                >

                <div class="dm-emails-extra">
                    <p class="dm-emails-extra__label"><strong>Emails adicionais</strong></p>
                    <p class="dm-header__sub">Recebem, em cópia, os emails internos enviados para a Smartlo.</p>
                    <div id="dm-emails-extra-list" class="dm-emails-extra__list">
                        <?php foreach (devolucoes_mentor_get_emails_internos_extra() as $extra_email) : ?>
                            <div class="dm-emails-extra__row">
                                <input
                                    type="email"
                                    class="dm-emails-subject"
                                    name="dm_emails_internos[]"
                                    value="<?php echo esc_attr($extra_email); ?>"
                                    placeholder="email@exemplo.com"
                                >
                                <button type="button" class="button button-link-delete dm-emails-extra__remove">Remover</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button button-secondary" id="dm-add-email-interno">+ Adicionar email</button>
                </div>
            </div>

            <?php
            $b2b_defaults = devolucoes_mentor_b2b_email_default_templates();
            $b2b_stored = get_option('dm_email_templates_b2b', []);
            if (!is_array($b2b_stored)) {
                $b2b_stored = [];
            }
            foreach ($b2b_defaults as $scenario_key => $scenario_emails) :
                $scenario_label = devolucoes_mentor_b2b_scenarios()[$scenario_key] ?? $scenario_key;
                ?>
                <div class="dm-emails-card">
                    <h2><?php echo esc_html($scenario_label); ?></h2>
                    <?php foreach ($scenario_emails as $email_key => $default_template) :
                        $saved = is_array($b2b_stored[$scenario_key][$email_key] ?? null)
                            ? $b2b_stored[$scenario_key][$email_key]
                            : [];
                        $subject_id = 'dm_b2b_subject_' . $scenario_key . '_' . $email_key;
                        $body_id = 'dm_b2b_body_' . $scenario_key . '_' . $email_key;
                        $label = $email_key === 'email_recebimento' ? 'Email receção (automático)' : 'Email ' . $email_key;
                        ?>
                        <div class="dm-emails-block">
                            <div class="dm-emails-block__head">
                                <h3><?php echo esc_html($label); ?></h3>
                                <button
                                    type="button"
                                    class="button button-secondary dm-btn-test-email"
                                    data-subject-id="<?php echo esc_attr($subject_id); ?>"
                                    data-body-id="<?php echo esc_attr($body_id); ?>"
                                >Enviar teste</button>
                            </div>
                            <p>
                                <label for="<?php echo esc_attr($subject_id); ?>"><strong>Assunto</strong></label>
                                <input
                                    type="text"
                                    class="dm-emails-subject"
                                    id="<?php echo esc_attr($subject_id); ?>"
                                    name="<?php echo esc_attr($subject_id); ?>"
                                    value="<?php echo esc_attr((string) ($saved['subject'] ?? $default_template['subject'])); ?>"
                                >
                            </p>
                            <p>
                                <label for="<?php echo esc_attr($body_id); ?>"><strong>Corpo</strong></label>
                                <?php
                                wp_editor(
                                    (string) ($saved['body'] ?? $default_template['body']),
                                    $body_id,
                                    [
                                        'textarea_name' => $body_id,
                                        'textarea_rows' => 10,
                                        'media_buttons' => false,
                                        'teeny'         => false,
                                        'quicktags'     => true,
                                    ]
                                );
                                ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <p class="submit">
                <button type="submit" name="dm_emails_save" class="button button-primary button-large">Guardar templates</button>
            </p>
        </form>
    </div>
    <?php
}

function devolucoes_mentor_ajax_preview_email(): void
{
    check_ajax_referer('dm_dashboard', 'nonce');

    $post_id   = absint($_POST['post_id'] ?? 0);
    $email_key = sanitize_key($_POST['email_key'] ?? '');

    $allowed_keys = ['email_1', 'email_2', 'email_3', 'email_4', 'email_5', 'email_6', 'email_recebimento'];

    if (!$post_id || !in_array($email_key, $allowed_keys, true)) {
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

    if (devolucoes_mentor_equipe_email_sending_disabled()) {
        wp_send_json_error(['message' => 'O envio de email pela equipa está desativado.'], 403);
    }

    $row = devolucoes_mentor_get_pedido_row($post_id);
    $template = devolucoes_mentor_get_pedido_email_template($row, $email_key);

    if ($template === null) {
        wp_send_json_error(['message' => 'Este email não se aplica à classificação do pedido.']);
    }

    if (!devolucoes_mentor_can_send_pedido_email($row, $email_key)) {
        wp_send_json_error(['message' => 'Este email já foi enviado.']);
    }

    if (isset($template['recipient']) && $template['recipient'] === 'smartlo') {
        $recipients = devolucoes_mentor_get_internal_recipients();
    } else {
        $recipients = array_filter([(string) $row['email_cliente']], 'is_email');
    }

    if ($recipients === []) {
        wp_send_json_error(['message' => 'Email de destino inválido ou em falta.']);
    }

    $to = implode(', ', $recipients);

    if ($template['group'] === 'troca_comercial' && $email_key === 'email_2') {
        $codigo = devolucoes_mentor_email_placeholders($row)['[CÓDIGO]'];
        if ($codigo === '') {
            wp_send_json_error(['message' => 'Preencha o Nº Recolha (código do vale) antes de enviar este email.']);
        }
    }

    wp_send_json_success([
        'to'      => $to,
        'subject' => $template['subject'],
        'body'    => $template['body'],
    ]);
}
add_action('wp_ajax_dm_preview_email', 'devolucoes_mentor_ajax_preview_email');

function devolucoes_mentor_ajax_send_email(): void
{
    check_ajax_referer('dm_dashboard', 'nonce');

    $post_id   = absint($_POST['post_id'] ?? 0);
    $email_key = sanitize_key($_POST['email_key'] ?? '');

    $allowed_keys = ['email_1', 'email_2', 'email_3', 'email_4', 'email_5', 'email_6', 'email_recebimento'];

    if (!$post_id || !in_array($email_key, $allowed_keys, true)) {
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

    if (devolucoes_mentor_equipe_email_sending_disabled()) {
        wp_send_json_error(['message' => 'O envio de email pela equipa está desativado.'], 403);
    }

    $custom_body = isset($_POST['email_body'])
        ? wp_kses_post(wp_unslash($_POST['email_body']))
        : '';

    if ($custom_body === '') {
        wp_send_json_error(['message' => 'O conteúdo do email não pode estar vazio.']);
    }

    $result = devolucoes_mentor_send_pedido_email($post_id, $email_key, $custom_body);

    if (!$result['success']) {
        wp_send_json_error(['message' => $result['message']]);
    }

    wp_send_json_success([
        'message' => $result['message'],
        'row'     => $result['row'],
    ]);
}
add_action('wp_ajax_dm_send_email', 'devolucoes_mentor_ajax_send_email');
