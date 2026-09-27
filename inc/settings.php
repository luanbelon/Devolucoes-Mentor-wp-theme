<?php
/**
 * Configuração — área exclusiva para administradores
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_option_disable_equipe_email(): bool
{
    return get_option('dm_disable_equipe_email', '0') === '1';
}

function devolucoes_mentor_option_hide_apoio_cliente_menu(): bool
{
    return get_option('dm_hide_apoio_cliente_menu', '0') === '1';
}

function devolucoes_mentor_equipe_email_sending_disabled(): bool
{
    return devolucoes_mentor_current_user_is_equipe_mentor()
        && devolucoes_mentor_option_disable_equipe_email();
}

function devolucoes_mentor_should_hide_apoio_cliente_for_current_user(): bool
{
    return devolucoes_mentor_current_user_is_equipe_mentor()
        && devolucoes_mentor_option_hide_apoio_cliente_menu();
}

function devolucoes_mentor_can_manage_settings(): bool
{
    return current_user_can('manage_options');
}

function devolucoes_mentor_settings_admin_menu(): void
{
    add_menu_page(
        'Configuração',
        'Configuração',
        'manage_options',
        'dm-configuracao',
        'devolucoes_mentor_render_settings_admin_page',
        'dashicons-admin-generic',
        29
    );
}
add_action('admin_menu', 'devolucoes_mentor_settings_admin_menu');

function devolucoes_mentor_enqueue_settings_admin_assets(string $hook): void
{
    if ($hook !== 'toplevel_page_dm-configuracao') {
        return;
    }

    $admin_css = DEVOLUCOES_MENTOR_DIR . '/assets/css/admin-dashboard.css';
    wp_enqueue_style(
        'devolucoes-mentor-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/css/admin-dashboard.css',
        [],
        file_exists($admin_css) ? (string) filemtime($admin_css) : DEVOLUCOES_MENTOR_VERSION
    );

    $settings_js = DEVOLUCOES_MENTOR_DIR . '/assets/js/settings-admin.js';
    wp_enqueue_script(
        'devolucoes-mentor-settings-admin',
        DEVOLUCOES_MENTOR_URI . '/assets/js/settings-admin.js',
        [],
        file_exists($settings_js) ? (string) filemtime($settings_js) : DEVOLUCOES_MENTOR_VERSION,
        true
    );

    wp_localize_script('devolucoes-mentor-settings-admin', 'dmSettingsAdmin', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('dm_settings'),
    ]);
}
add_action('admin_enqueue_scripts', 'devolucoes_mentor_enqueue_settings_admin_assets');

function devolucoes_mentor_render_settings_admin_page(): void
{
    if (!devolucoes_mentor_can_manage_settings()) {
        wp_die('Sem permissão.');
    }

    $disable_email = devolucoes_mentor_option_disable_equipe_email();
    $hide_apoio = devolucoes_mentor_option_hide_apoio_cliente_menu();
    ?>
    <div class="wrap dm-wrap dm-settings-wrap">
        <div class="dm-header">
            <div>
                <h1 class="dm-header__title">Configuração</h1>
                <p class="dm-header__sub">Definições da plataforma. Apenas administradores têm acesso a esta área.</p>
            </div>
        </div>

        <div class="dm-emails-card dm-settings-card">
            <div class="dm-settings-row">
                <div class="dm-settings-row__copy">
                    <h2>Desativar funcionalidade de envio de email pela equipa</h2>
                    <p>Quando ativo, os botões de envio de email nas tabelas de Pós Venda e Apoio Cliente ficam desativados para utilizadores Equipe Mentor.</p>
                </div>
                <label class="dm-toggle">
                    <input
                        type="checkbox"
                        class="dm-toggle__input"
                        data-setting="dm_disable_equipe_email"
                        <?php checked($disable_email); ?>
                    >
                    <span class="dm-toggle__track" aria-hidden="true"></span>
                    <span class="screen-reader-text">Desativar funcionalidade de envio de email pela equipa</span>
                </label>
            </div>
        </div>

        <div class="dm-emails-card dm-settings-card">
            <div class="dm-settings-row">
                <div class="dm-settings-row__copy">
                    <h2>Ocultar Menu apoio cliente</h2>
                    <p>Quando ativo, o menu Apoio Cliente deixa de ser visível para utilizadores Equipe Mentor.</p>
                </div>
                <label class="dm-toggle">
                    <input
                        type="checkbox"
                        class="dm-toggle__input"
                        data-setting="dm_hide_apoio_cliente_menu"
                        <?php checked($hide_apoio); ?>
                    >
                    <span class="dm-toggle__track" aria-hidden="true"></span>
                    <span class="screen-reader-text">Ocultar Menu apoio cliente</span>
                </label>
            </div>
        </div>

        <p class="dm-settings-feedback" id="dm-settings-feedback" hidden></p>
    </div>
    <?php
}

function devolucoes_mentor_ajax_save_setting(): void
{
    check_ajax_referer('dm_settings', 'nonce');

    if (!devolucoes_mentor_can_manage_settings()) {
        wp_send_json_error(['message' => 'Sem permissão.'], 403);
    }

    $key = sanitize_key($_POST['key'] ?? '');
    $allowed = ['dm_disable_equipe_email', 'dm_hide_apoio_cliente_menu'];

    if (!in_array($key, $allowed, true)) {
        wp_send_json_error(['message' => 'Opção inválida.']);
    }

    $raw = wp_unslash($_POST['value'] ?? '0');
    $value = ($raw === '1' || $raw === 'true' || $raw === true) ? '1' : '0';

    update_option($key, $value);

    wp_send_json_success([
        'message' => 'Configuração guardada.',
        'key'     => $key,
        'value'   => $value,
    ]);
}
add_action('wp_ajax_dm_save_setting', 'devolucoes_mentor_ajax_save_setting');
