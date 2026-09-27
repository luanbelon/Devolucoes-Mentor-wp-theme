<?php
/**
 * Templates de email B2B
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_b2b_email_recebimento_template(): array
{
    return [
        'subject' => 'Recebemos o seu pedido de assistência – Ref. [Nº Pedido]',
        'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pelo seu contacto.</p>
<p>Confirmamos a receção do seu pedido de assistência.</p>
<p>O seu pedido foi registado com a referência <strong>[Nº Pedido]</strong> e será analisado pela nossa equipa no prazo máximo de 48 horas úteis.</p>
<p>Caso seja necessária informação adicional, entraremos em contacto consigo.</p>
<p>Agradecemos a sua colaboração.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
    ];
}

function devolucoes_mentor_b2b_email_outro_templates(): array
{
    return [
        'email_2' => [
            'subject' => 'O seu pedido de assistência – Ref. [Nº Pedido]',
            'body'    => '<p>Olá [Nome],</p>
<p>Após análise da sua comunicação, precisamos que nos indique como pretende dar seguimento ao processo.</p>
<p><strong>Opção 1 – Ficar com o(s) artigo(s)</strong><br>
Neste caso, emitiremos a respetiva fatura correspondente aos artigos em questão.</p>
<p><strong>Opção 2 – Devolver o(s) artigo(s)</strong><br>
Neste caso, agendaremos a recolha dos artigos através da nossa transportadora, sem qualquer custo para si.</p>
<p>Agradecemos que responda a este email indicando a opção pretendida.</p>
<p>Caso tenha alguma questão adicional, estamos inteiramente disponíveis.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
        ],
        'email_3' => [
            'subject' => 'Regularização do pedido – Ref. [Nº Pedido]',
            'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder à regularização dos artigos através da emissão da respetiva fatura.</p>
<p><strong>N.º da Fatura:</strong> [FT-XXXX]</p>
<p>Não será necessária qualquer devolução dos artigos.</p>
<p>Caso tenha alguma questão adicional, permanecemos à disposição.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
        ],
        'email_4' => [
            'subject' => 'Pedido de recolha – Ref. [Nº Pedido]',
            'body'    => '<p>Olá,</p>
<p>O cliente pretende devolver os artigos associados a este pedido.</p>
<p>Solicitamos, por favor, o agendamento da recolha dos seguintes artigos:</p>
<p><strong>Pedido:</strong> [Nº Pedido]<br>
<strong>Artigos a recolher:</strong><br>
• [Produto]<br>
• [Quantidade]</p>
<p>Agradecemos confirmação do agendamento da recolha.</p>
<p>Obrigado.<br>Equipa Mentor Company</p>',
        ],
        'email_5' => [
            'subject' => 'Agendamento da recolha dos artigos – Ref. [Nº Pedido]',
            'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder à recolha dos artigos.</p>
<p>A nossa transportadora entrará em contacto consigo, caso necessário, para agendar a recolha.</p>
<p>Pedimos, por favor, que os artigos se encontrem devidamente acondicionados e prontos para entrega à transportadora.</p>
<p>Assim que a recolha estiver concluída, o processo será dado como encerrado.</p>
<p>Agradecemos a sua colaboração e lamentamos o incómodo causado.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
        ],
    ];
}

function devolucoes_mentor_b2b_email_default_templates(): array
{
    $recebimento = devolucoes_mentor_b2b_email_recebimento_template();

    return [
        'b2b_avaria' => [
            'email_recebimento' => $recebimento,
            'email_2' => [
                'subject' => 'Pedido de assistência concluído – Ref. [Nº Pedido]',
                'body'    => '<p>Olá [Nome],</p>
<p>Após análise da informação enviada, confirmamos que o seu pedido foi aprovado.</p>
<p>Foi emitida a respetiva Nota de Crédito n.º <strong>[NC-XXXX]</strong>.</p>
<p>Não será necessária a devolução do produto, pelo que poderá proceder à sua destruição de forma ambientalmente responsável.</p>
<p>Agradecemos a sua colaboração e permanecemos disponíveis para qualquer esclarecimento adicional.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
        ],
        'b2b_produto_falta' => [
            'email_recebimento' => $recebimento,
            'email_2' => [
                'subject' => 'Verificação de artigo em falta – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá,</p>
<p>Recebemos uma reclamação do cliente relativamente à encomenda <strong>[Nº Encomenda]</strong>, indicando que o seguinte artigo não foi recebido:</p>
<p><strong>Produto:</strong> [Nome do Produto]<br>
<strong>SKU/Referência:</strong> [SKU]<br>
<strong>Quantidade reclamada:</strong> [Qtd.]</p>
<p>Podem, por favor, verificar:</p>
<ul>
<li>Se o artigo foi efetivamente expedido;</li>
<li>Se existe algum registo de quebra, falta de stock ou separação parcial;</li>
<li>Se a encomenda foi enviada em mais do que um volume;</li>
<li>Se identificam alguma ocorrência durante a preparação da encomenda.</li>
</ul>
<p>Agradecemos que nos deem feedback com a maior brevidade possível para podermos responder ao cliente.</p>
<p>Obrigado.<br>Equipa Mentor Company</p>',
            ],
            'email_3' => [
                'subject' => 'Envio do artigo em falta – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Na sequência da sua comunicação, confirmamos que o artigo em falta já foi expedido.</p>
<p><strong>Detalhes do envio:</strong><br>
<strong>Encomenda:</strong> [Nº Encomenda]<br>
<strong>Produto:</strong> [Nome do Produto]<br>
<strong>Quantidade:</strong> [Qtd.]</p>
<p>O envio encontra-se em trânsito e será entregue através da nossa transportadora habitual.</p>
<p>Lamentamos o incómodo causado e agradecemos a sua compreensão.</p>
<p>Caso necessite de qualquer esclarecimento adicional, estamos inteiramente disponíveis.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
        ],
        'b2b_produto_errado' => [
            'email_recebimento' => $recebimento,
            'email_2' => [
                'subject' => 'Produto enviado por engano – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Após análise da sua comunicação, confirmámos que existiu um erro na preparação da sua encomenda e que foi expedido um produto diferente do solicitado.</p>
<p>Para podermos dar seguimento ao processo, agradecemos que nos indique qual das seguintes opções prefere:</p>
<p><strong>Opção 1</strong><br>
Ficar com o(s) produto(s) recebido(s) por engano e receber também o(s) artigo(s) correto(s). Neste caso, será emitida a respetiva fatura dos artigos adicionais recebidos.</p>
<p><strong>Opção 2</strong><br>
Devolver o(s) produto(s) enviado(s) por engano e receber apenas o(s) artigo(s) correto(s).</p>
<p>Assim que recebermos a sua resposta, daremos seguimento ao processo com a maior brevidade possível.</p>
<p>Lamentamos o incómodo causado e agradecemos a sua compreensão.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
            'email_3' => [
                'subject' => 'Expedição de artigos em falta – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá,</p>
<p>O cliente optou por manter os artigos enviados por engano.</p>
<p>Podem, por favor, proceder ao envio dos seguintes artigos em falta?</p>
<p><strong>Encomenda:</strong> [Nº Encomenda]<br>
<strong>Artigos a enviar:</strong><br>
• [Produto]<br>
• [Quantidade]</p>
<p>Assim que a expedição estiver concluída, agradecemos confirmação.</p>
<p>Obrigado,<br>Equipa Mentor Company</p>',
            ],
            'email_4' => [
                'subject' => 'Confirmação de envio dos artigos corretos – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder ao envio dos artigos corretos, mantendo os produtos inicialmente recebidos.</p>
<p>Os artigos encontram-se em preparação e serão expedidos com a maior brevidade.</p>
<p>Receberá a confirmação de envio e o respetivo número de seguimento assim que a expedição for efetuada.</p>
<p>A respetiva faturação dos artigos adicionais será emitida em conformidade.</p>
<p>Agradecemos a sua compreensão.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
            'email_5' => [
                'subject' => 'Recolha e nova expedição – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá,</p>
<p>O cliente pretende devolver os artigos enviados por engano.</p>
<p>Solicitamos, por favor:</p>
<ul>
<li>Agendamento da recolha dos artigos incorretamente enviados;</li>
<li>Expedição dos artigos corretos da encomenda.</li>
</ul>
<p><strong>Encomenda:</strong> [Nº Encomenda]<br>
<strong>Produtos Corretos:</strong> [SKU e Quantidade]</p>
<p>Assim que o processo estiver agendado, agradecemos confirmação.</p>
<p>Obrigado.<br>Equipa Mentor Company</p>',
            ],
            'email_6' => [
                'subject' => 'Recolha e envio dos artigos corretos – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder à recolha dos artigos enviados por engano e ao envio dos produtos corretos.</p>
<p>A nossa transportadora entrará em contacto consigo para agendar a recolha, caso aplicável.</p>
<p>Assim que os novos artigos forem expedidos, receberá a respetiva confirmação de envio e, se disponível, o número de seguimento.</p>
<p>Lamentamos o incómodo causado e agradecemos a sua compreensão.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
        ],
        'b2b_produto_excesso' => [
            'email_recebimento' => $recebimento,
            'email_2' => [
                'subject' => 'Produto enviado em excesso – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Após análise da sua comunicação, confirmámos que foram enviados artigos em quantidade superior à encomendada.</p>
<p>Para podermos concluir este processo, agradecemos que nos indique qual das seguintes opções pretende:</p>
<p><strong>Opção 1 – Ficar com os artigos recebidos em excesso</strong><br>
Neste caso, emitiremos a respetiva fatura correspondente aos artigos adicionais.</p>
<p><strong>Opção 2 – Devolver os artigos recebidos em excesso</strong><br>
Neste caso, agendaremos a recolha dos artigos através da nossa transportadora, sem qualquer custo para si.</p>
<p>Agradecemos que responda a este email indicando a opção pretendida.</p>
<p>Caso tenha alguma questão adicional, estamos inteiramente disponíveis.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
            'email_3' => [
                'subject' => 'Regularização da encomenda – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder à regularização dos artigos recebidos em excesso através da emissão da respetiva fatura.</p>
<p><strong>N.º da Fatura:</strong> [FT-XXXX]</p>
<p>Não será necessária qualquer devolução dos artigos.</p>
<p>Caso tenha alguma questão adicional, permanecemos à disposição.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
            'email_4' => [
                'subject' => 'Pedido de recolha – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá,</p>
<p>O cliente pretende devolver os artigos enviados em excesso.</p>
<p>Solicitamos, por favor, o agendamento da recolha dos seguintes artigos:</p>
<p><strong>Encomenda:</strong> [Nº Encomenda]<br>
<strong>Artigos a recolher:</strong><br>
• [Produto]<br>
• [Quantidade]</p>
<p>Agradecemos confirmação do agendamento da recolha.</p>
<p>Obrigado.<br>Equipa Mentor Company</p>',
            ],
            'email_5' => [
                'subject' => 'Agendamento da recolha dos artigos – Encomenda [Nº Encomenda]',
                'body'    => '<p>Olá [Nome],</p>
<p>Obrigado pela sua resposta.</p>
<p>Conforme solicitado, iremos proceder à recolha dos artigos enviados em excesso.</p>
<p>A nossa transportadora entrará em contacto consigo, caso necessário, para agendar a recolha.</p>
<p>Pedimos, por favor, que os artigos se encontrem devidamente acondicionados e prontos para entrega à transportadora.</p>
<p>Assim que a recolha estiver concluída, o processo será dado como encerrado.</p>
<p>Agradecemos a sua colaboração e lamentamos o incómodo causado.</p>
<p>Com os melhores cumprimentos,<br>Equipa Mentor Company</p>',
            ],
        ],
        'b2b_outro' => array_merge(
            ['email_recebimento' => $recebimento],
            devolucoes_mentor_b2b_email_outro_templates()
        ),
    ];
}

function devolucoes_mentor_b2b_email_recipient(string $scenario, string $email_key): string
{
    $rules = devolucoes_mentor_b2b_email_rules();
    $scenario_rules = $rules[$scenario] ?? [];

    foreach ($scenario_rules as $rule) {
        if ($rule['key'] === $email_key) {
            return $rule['recipient'] ?? 'cliente';
        }
    }

    if ($email_key === 'email_recebimento') {
        return 'cliente';
    }

    return 'cliente';
}

function devolucoes_mentor_get_smartlo_email(): string
{
    $email = get_option('dm_email_smartlo', '');
    if ($email !== '' && is_email($email)) {
        return $email;
    }

    return get_option('admin_email');
}

/**
 * Emails adicionais configurados para receber as comunicações internas.
 *
 * @return string[]
 */
function devolucoes_mentor_get_emails_internos_extra(): array
{
    $stored = get_option('dm_emails_internos', []);
    if (!is_array($stored)) {
        return [];
    }

    $emails = [];
    foreach ($stored as $email) {
        $email = is_string($email) ? trim($email) : '';
        if ($email !== '' && is_email($email)) {
            $emails[] = $email;
        }
    }

    return array_values(array_unique($emails));
}

/**
 * Destinatários internos: email da Smartlo + emails adicionais.
 *
 * @return string[]
 */
function devolucoes_mentor_get_internal_recipients(): array
{
    $recipients = array_merge(
        [devolucoes_mentor_get_smartlo_email()],
        devolucoes_mentor_get_emails_internos_extra()
    );

    $recipients = array_filter($recipients, static function ($email): bool {
        return is_string($email) && $email !== '' && (bool) is_email($email);
    });

    return array_values(array_unique($recipients));
}

function devolucoes_mentor_b2b_email_placeholders(array $row): array
{
    $base = devolucoes_mentor_email_placeholders($row);

    return array_merge($base, [
        '[Nº Pedido]'      => $row['pedido_id'] ?: '—',
        '[Nº Encomenda]'   => $row['num_encomenda'] ?: '—',
        '[Nome do Produto]' => $row['referencia'] ?: $row['sku'] ?: '—',
        '[SKU]'            => $row['sku'] ?: $row['referencia'] ?: '—',
        '[Qtd.]'           => (string) ($row['quantidade'] ?? ''),
        '[SKU e Quantidade]' => trim(($row['sku'] ?: $row['referencia']) . ' — Qtd. ' . ($row['quantidade'] ?? '')),
        '[Produto]'        => $row['referencia'] ?: $row['sku'] ?: '—',
        '[Quantidade]'     => (string) ($row['quantidade'] ?? ''),
        '[NC-XXXX]'        => (string) ($row['nota_credito_num'] ?? ''),
        '[FT-XXXX]'        => (string) ($row['fatura_num'] ?? ''),
    ]);
}

function devolucoes_mentor_b2b_get_email_template_raw(string $scenario, string $email_key): ?array
{
    $defaults = devolucoes_mentor_b2b_email_default_templates();
    $stored = get_option('dm_email_templates_b2b', []);

    if (!isset($defaults[$scenario][$email_key])) {
        return null;
    }

    $default = $defaults[$scenario][$email_key];
    $saved = is_array($stored[$scenario][$email_key] ?? null) ? $stored[$scenario][$email_key] : [];

    return [
        'subject' => (string) ($saved['subject'] ?? $default['subject']),
        'body'    => (string) ($saved['body'] ?? $default['body']),
    ];
}

function devolucoes_mentor_b2b_get_pedido_email_template(array $row, string $email_key): ?array
{
    $scenario = devolucoes_mentor_b2b_detect_scenario($row);
    if ($scenario === null) {
        return null;
    }

    $rules = devolucoes_mentor_b2b_email_rules();
    $scenario_rules = $rules[$scenario] ?? [];

    if ($email_key !== 'email_recebimento') {
        $found = false;
        foreach ($scenario_rules as $rule) {
            if ($rule['key'] === $email_key && devolucoes_mentor_b2b_email_rule_applies($rule, $row)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return null;
        }
    }

    $template = devolucoes_mentor_b2b_get_email_template_raw($scenario, $email_key);
    if ($template === null) {
        return null;
    }

    $placeholders = devolucoes_mentor_b2b_email_placeholders($row);

    return [
        'group'      => $scenario,
        'email_key'  => $email_key,
        'recipient'  => devolucoes_mentor_b2b_email_recipient($scenario, $email_key),
        'subject'    => devolucoes_mentor_apply_email_placeholders($template['subject'], $placeholders),
        'body'       => devolucoes_mentor_apply_email_placeholders($template['body'], $placeholders),
    ];
}

function devolucoes_mentor_b2b_email_sent_meta_key(string $email_key): string
{
    if ($email_key === 'email_recebimento') {
        return 'email_recebimento_enviado';
    }

    return devolucoes_mentor_email_meta_key($email_key);
}

function devolucoes_mentor_b2b_can_send_email(array $row, string $email_key): bool
{
    $meta_key = devolucoes_mentor_b2b_email_sent_meta_key($email_key);

    return empty($row[$meta_key]);
}

function devolucoes_mentor_b2b_send_email(int $post_id, string $email_key, string $custom_body = ''): array
{
    $row = devolucoes_mentor_get_pedido_row($post_id);
    $template = devolucoes_mentor_b2b_get_pedido_email_template($row, $email_key);

    if ($template === null) {
        return ['success' => false, 'message' => 'Template de email não disponível para este pedido.'];
    }

    if (!devolucoes_mentor_b2b_can_send_email($row, $email_key)) {
        return ['success' => false, 'message' => 'Este email já foi enviado.'];
    }

    $recipient_type = $template['recipient'];
    if ($recipient_type === 'smartlo') {
        $to = devolucoes_mentor_get_internal_recipients();
    } else {
        $to = array_filter([(string) $row['email_cliente']], 'is_email');
    }

    if ($to === []) {
        return ['success' => false, 'message' => 'Email de destino inválido ou em falta.'];
    }

    if (
        in_array($template['group'], ['b2b_produto_falta', 'b2b_produto_errado', 'b2b_produto_excesso'], true)
        && ($row['num_encomenda'] ?? '') === ''
    ) {
        return ['success' => false, 'message' => 'Preencha o Nº da encomenda (do cliente) antes de enviar este email.'];
    }

    if ($template['group'] === 'b2b_avaria' && $email_key === 'email_2') {
        if (($row['nota_credito_num'] ?? '') === '') {
            return ['success' => false, 'message' => 'Preencha o número da Nota de Crédito antes de enviar.'];
        }
    }

    if (
        in_array($email_key, ['email_3', 'email_4'], true)
        && in_array($template['group'], ['b2b_produto_excesso', 'b2b_produto_errado', 'b2b_outro'], true)
        && !empty($row['cliente_fica_produtos'])
        && ($row['fatura_num'] ?? '') === ''
    ) {
        return ['success' => false, 'message' => 'Preencha o número da Fatura antes de enviar.'];
    }

    $body_content = $custom_body !== '' ? $custom_body : $template['body'];
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $sent = wp_mail($to, $template['subject'], devolucoes_mentor_prepare_email_body($body_content), $headers);

    if (!$sent) {
        return ['success' => false, 'message' => 'Falha ao enviar o email.'];
    }

    $meta_key = devolucoes_mentor_b2b_email_sent_meta_key($email_key);
    update_post_meta($post_id, '_dm_' . $meta_key, '1');
    update_post_meta($post_id, '_dm_' . $meta_key . '_data', current_time('mysql'));

    return [
        'success' => true,
        'message' => 'Email enviado com sucesso.',
        'row'     => devolucoes_mentor_get_pedido_row($post_id),
    ];
}

function devolucoes_mentor_b2b_send_recebimento_email(int $post_id): void
{
    if (get_post_meta($post_id, '_dm_email_recebimento_enviado', true) === '1') {
        return;
    }

    devolucoes_mentor_b2b_send_email($post_id, 'email_recebimento');
}
