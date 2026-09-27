<?php
/**
 * B2B — cenários, colunas da tabela e estados
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_subtipos_erro_labels(): array
{
    return [
        'produto_falta'   => 'Produto em falta',
        'produto_errado'  => 'Produto errado',
        'produto_excesso' => 'Produto em excesso',
    ];
}

function devolucoes_mentor_b2b_scenarios(): array
{
    return [
        'b2b_avaria'          => 'Avaria reportada por cliente',
        'b2b_produto_falta'   => 'Erro na encomenda — Produto em falta',
        'b2b_produto_errado'  => 'Erro na encomenda — Produto errado',
        'b2b_produto_excesso' => 'Produto em excesso',
        'b2b_outro'           => 'Outro',
    ];
}

function devolucoes_mentor_b2b_estados(): array
{
    return [
        'pedido_recebido'         => 'Pedido Recebido',
        'em_verificacao'          => 'Em Verificação',
        'vale_emitido'            => 'Vale Emitido',
        'aguarda_resposta_cliente'=> 'Aguarda Resposta Cliente',
        'aguarda_resposta_smartlo'=> 'Aguarda Resposta Smartlo',
        'produto_enviado'         => 'Produto Enviado',
        'produtos_enviados'       => 'Produtos Enviados',
        'produtos_recolehidos'    => 'Produtos Recolhidos',
        'processo_concluido'      => 'Processo Concluído',
    ];
}

function devolucoes_mentor_normalize_subtipo_key(string $subtipo): string
{
    $subtipo = trim($subtipo);
    if ($subtipo === '') {
        return '';
    }

    $labels = devolucoes_mentor_subtipos_erro_labels();
    if (isset($labels[$subtipo])) {
        return $subtipo;
    }

    $lower = strtolower($subtipo);
    foreach ($labels as $key => $label) {
        if (strtolower($label) === $lower) {
            return $key;
        }
    }

    if (str_contains($lower, 'falta')) {
        return 'produto_falta';
    }
    if (str_contains($lower, 'errado')) {
        return 'produto_errado';
    }
    if (str_contains($lower, 'excesso') || str_contains($lower, 'mais')) {
        return 'produto_excesso';
    }

    return sanitize_key($subtipo);
}

function devolucoes_mentor_row_subtipo_key(array $row): string
{
    $subtipo = (string) ($row['subtipo'] ?? '');

    if ($subtipo === '') {
        $form = (string) ($row['tipo_pedido_form'] ?? '');
        if (preg_match('/\s*[—–\-]\s*(.+)$/u', $form, $matches)) {
            $subtipo = trim($matches[1]);
        }
    }

    return devolucoes_mentor_normalize_subtipo_key($subtipo);
}

function devolucoes_mentor_detect_tipo_pedido_key(array $row): string
{
    $key = (string) ($row['tipo_pedido_key'] ?? '');
    if ($key !== '') {
        return $key;
    }

    $form = (string) ($row['tipo_pedido_form'] ?? '');
    $lower = strtolower($form);

    if (str_contains($lower, 'avaria')) {
        return 'avaria_cliente';
    }
    if (str_contains($lower, 'erro')) {
        return 'erro_encomenda';
    }
    if (str_contains($lower, 'defeito')) {
        return 'defeito';
    }
    if (str_contains($lower, 'excesso')) {
        return 'produto_excesso';
    }
    if (str_contains($lower, 'outro')) {
        return 'outro';
    }

    return '';
}

function devolucoes_mentor_b2b_detect_scenario(array $row): ?string
{
    if (($row['canal'] ?? '') !== 'b2b') {
        return null;
    }

    $scenario = (string) ($row['b2b_scenario'] ?? '');
    if ($scenario !== '' && isset(devolucoes_mentor_b2b_scenarios()[$scenario])) {
        return $scenario;
    }

    $tipo_key = devolucoes_mentor_detect_tipo_pedido_key($row);
    $subtipo_key = devolucoes_mentor_row_subtipo_key($row);

    if ($tipo_key === 'avaria_cliente') {
        return 'b2b_avaria';
    }

    if ($tipo_key === 'produto_excesso') {
        return 'b2b_produto_excesso';
    }

    if ($tipo_key === 'outro') {
        return 'b2b_outro';
    }

    if ($tipo_key === 'erro_encomenda') {
        if ($subtipo_key === '') {
            return null;
        }

        return match ($subtipo_key) {
            'produto_falta'   => 'b2b_produto_falta',
            'produto_errado'  => 'b2b_produto_errado',
            'produto_excesso' => 'b2b_produto_excesso',
            default           => null,
        };
    }

    return null;
}

function devolucoes_mentor_b2b_email_rules(): array
{
    $excesso_rules = [
        ['key' => 'email_2', 'label' => 'Email (Opções Cliente)', 'recipient' => 'cliente'],
        ['key' => 'email_3', 'label' => 'Email (Cliente Fatura)', 'recipient' => 'cliente', 'when' => 'cliente_fica_produtos'],
        ['key' => 'email_4', 'label' => 'Email (Smartlo Pedido Recolha)', 'recipient' => 'smartlo', 'when_not' => 'cliente_fica_produtos'],
        ['key' => 'email_5', 'label' => 'Email (Cliente Recolha)', 'recipient' => 'cliente', 'when_not' => 'cliente_fica_produtos'],
    ];

    return [
        'b2b_avaria' => [
            ['key' => 'email_2', 'label' => 'Email (Devolução Aceite)', 'recipient' => 'cliente'],
        ],
        'b2b_produto_falta' => [
            ['key' => 'email_2', 'label' => 'Email (Smartlo Verificação)', 'recipient' => 'smartlo'],
            ['key' => 'email_3', 'label' => 'Email (Cliente Confirmação)', 'recipient' => 'cliente'],
        ],
        'b2b_produto_excesso' => $excesso_rules,
        'b2b_outro'           => $excesso_rules,
        'b2b_produto_errado' => [
            ['key' => 'email_2', 'label' => 'Email (Opções Cliente)', 'recipient' => 'cliente'],
            ['key' => 'email_3', 'label' => 'Email (Smartlo Expedição Artigos Falta)', 'recipient' => 'smartlo', 'when' => 'cliente_fica_produtos'],
            ['key' => 'email_4', 'label' => 'Email (Cliente Expedição e Fatura)', 'recipient' => 'cliente', 'when' => 'cliente_fica_produtos'],
            ['key' => 'email_5', 'label' => 'Email (Smartlo Recolha e Expedição)', 'recipient' => 'smartlo', 'when_not' => 'cliente_fica_produtos'],
            ['key' => 'email_6', 'label' => 'Email (Cliente Recolha e Expedição)', 'recipient' => 'cliente', 'when_not' => 'cliente_fica_produtos'],
        ],
    ];
}

function devolucoes_mentor_b2b_email_rule_applies(array $rule, array $row): bool
{
    if (!empty($rule['when'])) {
        $field = $rule['when'];
        if (empty($row[$field])) {
            return false;
        }
    }

    if (!empty($rule['when_not'])) {
        $field = $rule['when_not'];
        if (!empty($row[$field])) {
            return false;
        }
    }

    return true;
}

function devolucoes_mentor_b2b_column_applies(array $column, array $row): bool
{
    if (!empty($column['when'])) {
        $field = $column['when'];
        if (empty($row[$field])) {
            return false;
        }
    }

    if (!empty($column['when_not'])) {
        $field = $column['when_not'];
        if (!empty($row[$field])) {
            return false;
        }
    }

    return true;
}

function devolucoes_mentor_b2b_base_columns(): array
{
    return [
        ['key' => 'pedido_id', 'label' => 'ID', 'type' => 'id'],
        ['key' => 'data_pedido', 'label' => 'Data de Submissão', 'type' => 'date'],
        ['key' => 'estado', 'label' => 'Estado do Pedido', 'type' => 'select_estado', 'field' => 'estado'],
        ['key' => 'responsavel', 'label' => 'Responsável', 'type' => 'select_user', 'field' => 'responsavel'],
        ['key' => 'dias_em_processo', 'label' => 'Dias em Processo', 'type' => 'days'],
        ['key' => 'empresa', 'label' => 'Empresa', 'type' => 'readonly'],
        ['key' => 'nif', 'label' => 'NIF', 'type' => 'readonly'],
        ['key' => 'contacto', 'label' => 'Pessoa de contacto', 'type' => 'readonly', 'fallback' => 'nome_cliente'],
        ['key' => 'email_cliente', 'label' => 'Email', 'type' => 'readonly'],
        ['key' => 'telefone', 'label' => 'Telefone', 'type' => 'readonly'],
        ['key' => 'marca', 'label' => 'Marca', 'type' => 'readonly'],
        ['key' => 'num_encomenda', 'label' => 'Nº encomenda', 'type' => 'text', 'field' => 'num_encomenda'],
        ['key' => 'referencia', 'label' => 'Referência Produto', 'type' => 'text', 'field' => 'sku', 'fallback' => 'sku'],
        ['key' => 'quantidade', 'label' => 'Quantidade', 'type' => 'readonly'],
        ['key' => 'descricao', 'label' => 'Descrição do Problema', 'type' => 'readonly_multiline'],
        ['key' => 'fotografias', 'label' => 'Fotos', 'type' => 'files', 'files_prefix' => 'Foto'],
        ['key' => 'videos', 'label' => 'Vídeos', 'type' => 'files', 'files_prefix' => 'Vídeo'],
        ['key' => 'documentos', 'label' => 'Documentos', 'type' => 'files', 'files_prefix' => 'Doc'],
    ];
}

function devolucoes_mentor_b2b_workflow_columns(string $scenario): array
{
    switch ($scenario) {
        case 'b2b_avaria':
            return [
                ['key' => 'nota_credito_num', 'label' => 'Nota de Crédito', 'type' => 'text', 'field' => 'nota_credito_num', 'placeholder' => 'NC-001'],
                ['key' => 'col_email2_avaria', 'label' => 'Email (Devolução Aceite)', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
            ];

        case 'b2b_produto_excesso':
        case 'b2b_outro':
            return [
                ['key' => 'col_email2_excesso', 'label' => 'Email (Opções Cliente)', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
                ['key' => 'cliente_fica_produtos', 'label' => 'Cliente Fica c/ Produtos', 'type' => 'checkbox', 'field' => 'cliente_fica_produtos'],
                ['key' => 'fatura_num', 'label' => 'Fatura', 'type' => 'text', 'field' => 'fatura_num', 'placeholder' => 'FC-001'],
                ['key' => 'col_email3_excesso_fatura', 'label' => 'Email (Cliente Fatura)', 'type' => 'email_button', 'sent_key' => 'email_3_enviado', 'email_key' => 'email_3', 'when' => 'cliente_fica_produtos'],
                ['key' => 'col_email4_excesso_smartlo', 'label' => 'Email (Smartlo Pedido Recolha)', 'type' => 'email_button', 'sent_key' => 'email_4_enviado', 'email_key' => 'email_4', 'when_not' => 'cliente_fica_produtos'],
                ['key' => 'col_email5_excesso_cliente', 'label' => 'Email (Cliente Recolha)', 'type' => 'email_button', 'sent_key' => 'email_5_enviado', 'email_key' => 'email_5', 'when_not' => 'cliente_fica_produtos'],
            ];

        case 'b2b_produto_falta':
            return [
                ['key' => 'col_email2_falta_smartlo', 'label' => 'Email (Smartlo Verificação)', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
                ['key' => 'produto_falta_confirmado', 'label' => 'Produto em falta', 'type' => 'checkbox', 'field' => 'produto_falta_confirmado'],
                ['key' => 'col_email3_falta_cliente', 'label' => 'Email (Cliente Confirmação)', 'type' => 'email_button', 'sent_key' => 'email_3_enviado', 'email_key' => 'email_3'],
                ['key' => 'pedido_envio', 'label' => 'Pedido de Envio', 'type' => 'checkbox', 'field' => 'pedido_envio'],
            ];

        case 'b2b_produto_errado':
            return [
                ['key' => 'col_email2_errado', 'label' => 'Email (Opções Cliente)', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
                ['key' => 'cliente_fica_produtos', 'label' => 'Cliente Fica c/ Produtos', 'type' => 'checkbox', 'field' => 'cliente_fica_produtos'],
                ['key' => 'fatura_num', 'label' => 'Fatura', 'type' => 'text', 'field' => 'fatura_num', 'placeholder' => 'FC-001'],
                ['key' => 'col_email3_errado_smartlo', 'label' => 'Email (Smartlo Expedição Artigos Falta)', 'type' => 'email_button', 'sent_key' => 'email_3_enviado', 'email_key' => 'email_3', 'when' => 'cliente_fica_produtos'],
                ['key' => 'col_email4_errado_cliente', 'label' => 'Email (Cliente Expedição e Fatura)', 'type' => 'email_button', 'sent_key' => 'email_4_enviado', 'email_key' => 'email_4', 'when' => 'cliente_fica_produtos'],
                ['key' => 'col_email5_errado_smartlo', 'label' => 'Email (Smartlo Recolha e Expedição)', 'type' => 'email_button', 'sent_key' => 'email_5_enviado', 'email_key' => 'email_5', 'when_not' => 'cliente_fica_produtos'],
                ['key' => 'col_email6_errado_cliente', 'label' => 'Email (Cliente Recolha e Expedição)', 'type' => 'email_button', 'sent_key' => 'email_6_enviado', 'email_key' => 'email_6', 'when_not' => 'cliente_fica_produtos'],
            ];

        default:
            return [];
    }
}

function devolucoes_mentor_b2b_dashboard_columns_for_scenario(string $scenario): array
{
    return array_merge(
        devolucoes_mentor_b2b_base_columns(),
        devolucoes_mentor_b2b_workflow_columns($scenario)
    );
}

/** @deprecated Use devolucoes_mentor_b2b_dashboard_columns_for_scenario() */
function devolucoes_mentor_b2b_dashboard_columns(): array
{
    return devolucoes_mentor_b2b_dashboard_columns_for_scenario('b2b_avaria');
}

function devolucoes_mentor_b2b_visible_columns(array $row): array
{
    $columns = devolucoes_mentor_b2b_dashboard_columns();
    $visible = [];

    foreach ($columns as $column) {
        if (devolucoes_mentor_b2b_column_applies($column, $row)) {
            $visible[] = $column;
        }
    }

    return $visible;
}

function devolucoes_mentor_b2b_row_meta_fields(): array
{
    return [
        'tipo_pedido_key', 'b2b_scenario', 'vale_emitido', 'nota_credito_num',
        'cliente_fica_produtos', 'fatura_num', 'produto_falta_confirmado', 'pedido_envio',
        'vale_codigo', 'email_recebimento_enviado',
        'email_4_enviado', 'email_5_enviado', 'email_6_enviado',
        'email_4_enviado_data', 'email_5_enviado_data', 'email_6_enviado_data',
    ];
}
