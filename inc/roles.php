<?php
/**
 * Role — Equipe Mentor
 */

if (!defined('ABSPATH')) {
    exit;
}

function devolucoes_mentor_role_caps_for_canal(string $canal): array
{
    if ($canal === 'b2c') {
        return [
            'read_pedido_devolucao_b2c'             => true,
            'read_private_pedidos_devolucao_b2c'    => true,
            'edit_pedido_devolucao_b2c'             => true,
            'edit_pedidos_devolucao_b2c'            => true,
            'edit_others_pedidos_devolucao_b2c'     => true,
            'edit_private_pedidos_devolucao_b2c'    => true,
            'edit_published_pedidos_devolucao_b2c'  => true,
            'publish_pedidos_devolucao_b2c'         => true,
            'delete_pedido_devolucao_b2c'           => true,
            'delete_pedidos_devolucao_b2c'          => true,
            'delete_others_pedidos_devolucao_b2c'   => true,
            'delete_private_pedidos_devolucao_b2c'  => true,
            'delete_published_pedidos_devolucao_b2c'  => true,
        ];
    }

    return [
        'read_pedido_devolucao'             => true,
        'read_private_pedidos_devolucao'    => true,
        'edit_pedido_devolucao'             => true,
        'edit_pedidos_devolucao'            => true,
        'edit_others_pedidos_devolucao'     => true,
        'edit_private_pedidos_devolucao'    => true,
        'edit_published_pedidos_devolucao'  => true,
        'publish_pedidos_devolucao'         => true,
        'delete_pedido_devolucao'           => true,
        'delete_pedidos_devolucao'          => true,
        'delete_others_pedidos_devolucao'   => true,
        'delete_private_pedidos_devolucao'  => true,
        'delete_published_pedidos_devolucao'=> true,
    ];
}

function devolucoes_mentor_register_role(): void
{
    $caps = [
        'read'                  => true,
        'dm_access_devolucoes'  => true,
    ];

    foreach (array_keys(devolucoes_mentor_canais()) as $canal) {
        $caps = array_merge($caps, devolucoes_mentor_role_caps_for_canal($canal));
    }

    $role = get_role('equipe_mentor');

    if (!$role) {
        add_role('equipe_mentor', 'Equipe Mentor', $caps);
    } else {
        foreach ($caps as $cap => $grant) {
            $role->add_cap($cap, $grant);
        }
    }

    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('dm_access_devolucoes', true);
        foreach (array_keys(devolucoes_mentor_canais()) as $canal) {
            foreach (devolucoes_mentor_role_caps_for_canal($canal) as $cap => $grant) {
                $admin->add_cap($cap, $grant);
            }
        }
    }
}
add_action('after_switch_theme', 'devolucoes_mentor_register_role');
add_action('init', 'devolucoes_mentor_register_role', 5);
add_action('admin_init', 'devolucoes_mentor_register_role', 1);

/**
 * Garante que administradores têm sempre as capabilities dos pedidos.
 */
function devolucoes_mentor_grant_admin_caps(array $allcaps, array $caps, array $args, \WP_User $user): array
{
    if (!in_array('administrator', (array) $user->roles, true)) {
        return $allcaps;
    }

    foreach (array_keys(devolucoes_mentor_canais()) as $canal) {
        foreach (devolucoes_mentor_role_caps_for_canal($canal) as $cap => $grant) {
            if ($grant) {
                $allcaps[$cap] = true;
            }
        }
    }

    $allcaps['dm_access_devolucoes'] = true;

    return $allcaps;
}
add_filter('user_has_cap', 'devolucoes_mentor_grant_admin_caps', 10, 4);

function devolucoes_mentor_current_user_is_equipe_mentor(): bool
{
    if (current_user_can('manage_options')) {
        return false;
    }

    $user = wp_get_current_user();

    return $user instanceof WP_User && in_array('equipe_mentor', (array) $user->roles, true);
}

function devolucoes_mentor_can_manage_pedidos(?string $canal = null): bool
{
    if (
        $canal === 'b2c'
        && function_exists('devolucoes_mentor_should_hide_apoio_cliente_for_current_user')
        && devolucoes_mentor_should_hide_apoio_cliente_for_current_user()
    ) {
        return false;
    }

    if (current_user_can('manage_options')) {
        return true;
    }

    if ($canal !== null) {
        $config = devolucoes_mentor_canal_config($canal);
        if ($config && current_user_can($config['capability'])) {
            return true;
        }
    }

    if (current_user_can('dm_access_devolucoes')) {
        return true;
    }

    if ($canal !== null) {
        return false;
    }

    foreach (devolucoes_mentor_canais() as $config) {
        if (current_user_can($config['capability'])) {
            return true;
        }
    }

    return false;
}

function devolucoes_mentor_can_delete_pedidos(?string $canal = null): bool
{
    if (!devolucoes_mentor_can_manage_pedidos($canal)) {
        return false;
    }

    if (current_user_can('manage_options')) {
        return true;
    }

    if ($canal === 'b2c') {
        return current_user_can('delete_pedidos_devolucao_b2c');
    }

    if ($canal === 'b2b') {
        return current_user_can('delete_pedidos_devolucao');
    }

    return current_user_can('delete_pedidos_devolucao')
        || current_user_can('delete_pedidos_devolucao_b2c');
}

function devolucoes_mentor_can_delete_pedido(int $post_id): bool
{
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, devolucoes_mentor_post_types(), true)) {
        return false;
    }

    $canal = devolucoes_mentor_canal_for_post_type($post_type);

    return devolucoes_mentor_can_delete_pedidos($canal)
        && current_user_can('delete_post', $post_id);
}
