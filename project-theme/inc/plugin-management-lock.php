<?php

add_filter( 'user_has_cap', 'project_theme_block_plugin_management_caps', 20 );
function project_theme_block_plugin_management_caps( array $allcaps ): array {
    $blocked_caps = array(
        'update_plugins',
        'install_plugins',
        'delete_plugins',
        'edit_plugins',
        'edit_themes',
        'install_themes',
        'update_themes',
        'delete_themes',
        'create_users',
        'promote_users',
    );

    foreach ( $blocked_caps as $cap ) {
        $allcaps[ $cap ] = false;
    }

    return $allcaps;
}

add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', 'project_theme_remove_xmlrpc_header' );
function project_theme_remove_xmlrpc_header( array $headers ): array {
    unset( $headers['X-Pingback'] );

    return $headers;
}

add_filter( 'bloginfo_url', 'project_theme_remove_pingback_url', 10, 2 );
function project_theme_remove_pingback_url( string $output, string $show ): string {
    if ( 'pingback_url' === $show ) {
        return '';
    }

    return $output;
}

add_action( 'admin_menu', 'project_theme_remove_blocked_admin_pages', 999 );
function project_theme_remove_blocked_admin_pages(): void {
    remove_submenu_page( 'themes.php', 'theme-editor.php' );
    remove_submenu_page( 'themes.php', 'theme-install.php' );
    remove_submenu_page( 'users.php', 'user-new.php' );
}

add_action( 'admin_init', 'project_theme_block_plugin_activation_actions' );
add_action( 'admin_init', 'project_theme_block_theme_and_user_actions' );
function project_theme_block_plugin_activation_actions(): void {
    global $pagenow;

    if ( 'plugins.php' !== $pagenow || empty( $_REQUEST['action'] ) ) {
        return;
    }

    $blocked_actions = array(
        'activate',
        'deactivate',
        'activate-selected',
        'deactivate-selected',
    );

    if ( in_array( (string) $_REQUEST['action'], $blocked_actions, true ) ) {
        wp_die(
            esc_html__( 'Plugin activation and deactivation are disabled by the theme.', 'project-themes' ),
            esc_html__( 'Plugin management disabled', 'project-themes' ),
            array( 'response' => 403 )
        );
    }
}

function project_theme_block_theme_and_user_actions(): void {
    global $pagenow;

    $blocked_pages = array(
        'theme-editor.php',
        'theme-install.php',
        'user-new.php',
    );

    if ( in_array( $pagenow, $blocked_pages, true ) ) {
        wp_die(
            esc_html__( 'This admin action is disabled by the theme.', 'project-themes' ),
            esc_html__( 'Admin action disabled', 'project-themes' ),
            array( 'response' => 403 )
        );
    }

    if ( 'themes.php' !== $pagenow || empty( $_REQUEST['action'] ) ) {
        return;
    }

    $blocked_actions = array(
        'activate',
        'delete',
        'update-selected',
        'delete-selected',
    );

    if ( in_array( (string) $_REQUEST['action'], $blocked_actions, true ) ) {
        wp_die(
            esc_html__( 'Theme management actions are disabled by the theme.', 'project-themes' ),
            esc_html__( 'Theme management disabled', 'project-themes' ),
            array( 'response' => 403 )
        );
    }
}

add_filter( 'site_transient_update_plugins', 'project_theme_hide_plugin_updates' );
function project_theme_hide_plugin_updates( $transient ) {
    if ( is_object( $transient ) ) {
        $transient->response = array();
        $transient->no_update = array();
    }

    return $transient;
}

add_filter( 'site_transient_update_themes', 'project_theme_hide_theme_updates' );
function project_theme_hide_theme_updates( $transient ) {
    if ( is_object( $transient ) ) {
        $transient->response = array();
        $transient->no_update = array();
    }

    return $transient;
}

add_filter( 'plugin_action_links', 'project_theme_remove_plugin_management_links', 20 );
function project_theme_remove_plugin_management_links( array $actions ): array {
    unset(
        $actions['activate'],
        $actions['deactivate'],
        $actions['delete'],
        $actions['edit']
    );

    return $actions;
}

add_filter( 'theme_action_links', 'project_theme_remove_theme_management_links', 20 );
function project_theme_remove_theme_management_links( array $actions ): array {
    unset(
        $actions['activate'],
        $actions['delete']
    );

    return $actions;
}
