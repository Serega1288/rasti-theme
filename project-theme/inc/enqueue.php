<?php

function project_theme_enqueue_assets(): void
{
    $theme_version    = wp_get_theme()->get('Version');
    $css_path         = get_template_directory() . '/assets/css/main.css';
    $js_path          = get_template_directory() . '/assets/js/main.js';
    $swiper_css_path  = get_template_directory() . '/assets/vendor/swiper/swiper-bundle.min.css';
    $swiper_js_path   = get_template_directory() . '/assets/vendor/swiper/swiper-bundle.min.js';
    $fancybox_css_path = get_template_directory() . '/assets/vendor/fancybox/fancybox.css';
    $fancybox_js_path  = get_template_directory() . '/assets/vendor/fancybox/fancybox.umd.js';
    $swup_js_path            = get_template_directory() . '/assets/vendor/swup/swup.umd.js';
    $swup_preload_js_path    = get_template_directory() . '/assets/vendor/swup/swup-preload-plugin.umd.js';
    $css_version      = file_exists( $css_path ) ? filemtime( $css_path ) : $theme_version;
    $js_version       = file_exists( $js_path ) ? filemtime( $js_path ) : $theme_version;
    $swiper_css_ver   = file_exists( $swiper_css_path ) ? filemtime( $swiper_css_path ) : $theme_version;
    $swiper_js_ver    = file_exists( $swiper_js_path ) ? filemtime( $swiper_js_path ) : $theme_version;
    $fancybox_css_ver = file_exists( $fancybox_css_path ) ? filemtime( $fancybox_css_path ) : $theme_version;
    $fancybox_js_ver  = file_exists( $fancybox_js_path ) ? filemtime( $fancybox_js_path ) : $theme_version;
    $swup_js_ver             = file_exists( $swup_js_path ) ? filemtime( $swup_js_path ) : $theme_version;
    $swup_preload_js_ver     = file_exists( $swup_preload_js_path ) ? filemtime( $swup_preload_js_path ) : $theme_version;
    $script_deps      = array( 'jquery', 'swup', 'swup-preload-plugin', 'swiper', 'fancybox' );

    wp_enqueue_script( 'selectWoo' );
    wp_enqueue_style( 'select2' );
    wp_enqueue_script( 'wc-country-select' );
    wp_enqueue_script( 'wc-address-i18n' );

//    wp_enqueue_script( 'wc-checkout' );

//    if ( wp_script_is( 'wc-checkout', 'registered' ) ) {
//        wp_enqueue_script( 'wc-checkout' );
//    }

    $script_deps[] = 'selectWoo';
    $script_deps[] = 'wc-country-select';
    $script_deps[] = 'wc-address-i18n';

    wp_enqueue_style(
        'rasti-theme-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array( 'swiper' ),
        $css_version
    );

    wp_enqueue_style(
        'swiper',
        get_template_directory_uri() . '/assets/vendor/swiper/swiper-bundle.min.css',
        array(),
        $swiper_css_ver
    );

    wp_enqueue_style(
        'fancybox',
        get_template_directory_uri() . '/assets/vendor/fancybox/fancybox.css',
        array(),
        $fancybox_css_ver
    );

    wp_enqueue_script(
        'swup',
        get_template_directory_uri() . '/assets/vendor/swup/swup.umd.js',
        array(),
        $swup_js_ver,
        true
    );

    wp_enqueue_script(
        'swup-preload-plugin',
        get_template_directory_uri() . '/assets/vendor/swup/swup-preload-plugin.umd.js',
        array( 'swup' ),
        $swup_preload_js_ver,
        true
    );

    wp_enqueue_script(
        'swiper',
        get_template_directory_uri() . '/assets/vendor/swiper/swiper-bundle.min.js',
        array(),
        $swiper_js_ver,
        true
    );

    wp_enqueue_script(
        'fancybox',
        get_template_directory_uri() . '/assets/vendor/fancybox/fancybox.umd.js',
        array(),
        $fancybox_js_ver,
        true
    );

    wp_enqueue_script(
        'rasti-theme-main',
        get_template_directory_uri() . '/assets/js/main.js',
        array_values( array_unique( $script_deps ) ),
        $js_version,
        true
    );

    wp_localize_script(
        'rasti-theme-main',
        'rastiTheme',
        array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'ajaxNonce'   => wp_create_nonce( 'rasti-theme-ajax' ),
            'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
            'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '',
            'siteLock'    => function_exists( 'project_theme_get_site_lock_config' ) ? project_theme_get_site_lock_config() : array(),
        )
    );
}
add_action('wp_enqueue_scripts', 'project_theme_enqueue_assets');

function project_theme_dequeue_waitlist_styles(): void
{
    $style_handles = [
        'xoo-wl-style',
        'xoo-wl-fonts',
        'xoo-aff-style',
        'xoo-aff-font-awesome5',
        'xoo-aff-flags',
        'jquery-ui-css',
        'xoo-select2',
    ];

    foreach ( $style_handles as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }
}
add_action('wp_enqueue_scripts', 'project_theme_dequeue_waitlist_styles', 999);
