<?php
/**
 * B2C — colunas da tabela por classificação
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_b2c_base_columns(): array
{
    return [
        ['key' => 'pedido_id', 'label' => 'ID', 'type' => 'id'],
        ['key' => 'data_pedido', 'label' => 'Data de Submissão', 'type' => 'date'],
        ['key' => 'estado', 'label' => 'Estado', 'type' => 'select_estado', 'field' => 'estado'],
        ['key' => 'responsavel', 'label' => 'Responsável', 'type' => 'select_user', 'field' => 'responsavel'],
        ['key' => 'dias_em_processo', 'label' => 'Dias em processo', 'type' => 'days'],
        ['key' => 'nome_cliente', 'label' => 'Nome completo', 'type' => 'readonly'],
        ['key' => 'email_cliente', 'label' => 'Email', 'type' => 'readonly'],
        ['key' => 'telefone', 'label' => 'Telefone', 'type' => 'readonly'],
        ['key' => 'nif', 'label' => 'NIF', 'type' => 'readonly'],
        ['key' => 'num_encomenda', 'label' => 'Nº da encomenda', 'type' => 'text', 'field' => 'num_encomenda'],
        ['key' => 'marca', 'label' => 'Marca', 'type' => 'readonly'],
        ['key' => 'referencia', 'label' => 'Referência', 'type' => 'text', 'field' => 'sku', 'fallback' => 'sku'],
        ['key' => 'quantidade', 'label' => 'Quantidade', 'type' => 'readonly'],
        ['key' => 'descricao', 'label' => 'Descrição do problema', 'type' => 'readonly_multiline'],
        ['key' => 'fotografias', 'label' => 'Fotografias', 'type' => 'files', 'files_prefix' => 'Foto'],
        ['key' => 'videos', 'label' => 'Vídeos', 'type' => 'files', 'files_prefix' => 'Vídeo'],
        ['key' => 'documentos', 'label' => 'Documentos', 'type' => 'files', 'files_prefix' => 'Doc'],
        ['key' => 'motivo', 'label' => 'Motivo', 'type' => 'textarea', 'field' => 'motivo'],
        ['key' => 'observacoes', 'label' => 'Observações', 'type' => 'textarea', 'field' => 'observacoes'],
    ];
}

function devolucoes_mentor_b2c_workflow_columns(string $tipo): array
{
    switch ($tipo) {
        case 'direito_livre_resolucao':
        case 'pedido_fora_prazo':
            return [
                ['key' => 'valor', 'label' => 'Valor', 'type' => 'text', 'field' => 'valor', 'placeholder' => '0,00'],
                ['key' => 'email_1_enviado', 'label' => 'Email 1 — Receção devolução', 'type' => 'email_button', 'sent_key' => 'email_1_enviado', 'email_key' => 'email_1'],
                ['key' => 'data_rececao_smartlo', 'label' => 'Data receção SmartLo', 'type' => 'date_input', 'field' => 'data_rececao_smartlo'],
                ['key' => 'email_2_enviado', 'label' => 'Email 2 — Reembolso aprovado', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
                ['key' => 'data_reembolso', 'label' => 'Data do reembolso', 'type' => 'date_input', 'field' => 'data_reembolso'],
                ['key' => 'nota_credito_emitida', 'label' => 'Nota de crédito emitida', 'type' => 'checkbox', 'field' => 'nota_credito_emitida'],
                ['key' => 'email_3_enviado', 'label' => 'Email 3 — Recusa / fora prazo', 'type' => 'email_button', 'sent_key' => 'email_3_enviado', 'email_key' => 'email_3'],
            ];

        case 'troca_comercial':
            return [
                ['key' => 'valor', 'label' => 'Valor do vale', 'type' => 'text', 'field' => 'valor', 'placeholder' => '0,00'],
                ['key' => 'num_recolha', 'label' => 'Código do vale', 'type' => 'text', 'field' => 'num_recolha', 'placeholder' => 'Código'],
                ['key' => 'email_1_enviado', 'label' => 'Email 1 — Troca aprovada', 'type' => 'email_button', 'sent_key' => 'email_1_enviado', 'email_key' => 'email_1'],
                ['key' => 'email_2_enviado', 'label' => 'Email 2 — Vale de compra', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
            ];

        case 'produto_defeituoso':
        case 'erro_expedicao':
            return [
                ['key' => 'valor', 'label' => 'Valor', 'type' => 'text', 'field' => 'valor', 'placeholder' => '0,00'],
                ['key' => 'num_recolha', 'label' => 'Nº Recolha', 'type' => 'text', 'field' => 'num_recolha'],
                ['key' => 'email_1_enviado', 'label' => 'Email 1 — Pedido de informação', 'type' => 'email_button', 'sent_key' => 'email_1_enviado', 'email_key' => 'email_1'],
                ['key' => 'email_2_enviado', 'label' => 'Email 2 — Recolha aprovada', 'type' => 'email_button', 'sent_key' => 'email_2_enviado', 'email_key' => 'email_2'],
                ['key' => 'email_3_enviado', 'label' => 'Email 3 — Reembolso processado', 'type' => 'email_button', 'sent_key' => 'email_3_enviado', 'email_key' => 'email_3'],
            ];

        default:
            return [];
    }
}

function devolucoes_mentor_b2c_dashboard_columns_for_tipo(string $tipo): array
{
    return array_merge(
        devolucoes_mentor_b2c_base_columns(),
        devolucoes_mentor_b2c_workflow_columns($tipo)
    );
}
