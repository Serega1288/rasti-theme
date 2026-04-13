<?php

function project_theme_enqueue_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $css_path      = get_template_directory() . '/assets/css/main.css';
    $js_path       = get_template_directory() . '/assets/js/main.js';
    $css_version   = file_exists( $css_path ) ? filemtime( $css_path ) : $theme_version;
    $js_version    = file_exists( $js_path ) ? filemtime( $js_path ) : $theme_version;

    wp_enqueue_style(
        'rasti-theme-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array(),
        $css_version
    );

    wp_enqueue_script(
        'swup',
        'https://unpkg.com/swup@4',
        array(),
        null,
        true
    );

    wp_enqueue_script(
        'rasti-theme-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array( 'jquery', 'swup' ),
        $js_version,
        true
    );
}
add_action('wp_enqueue_scripts', 'project_theme_enqueue_assets');
