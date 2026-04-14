<?php
/**
 * Maintenance mode module.
 *
 * This file is kept separate from the main theme bootstrap so the
 * maintenance logic stays isolated and can be enabled or adjusted safely.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function brackets_get_maintenance_page(): ?WP_Post {
    if ( ! function_exists( 'get_field' ) ) {
        return null;
    }

    $field_names = [
        'brackets-page',
        'brackets_page',
        'maintenance-page',
        'maintenance_page',
        'stub-page',
        'stub_page',
        'active-brackets-page',
    ];

    foreach ( $field_names as $field_name ) {
        $value = get_field( $field_name, 'option' );

        if ( $value instanceof WP_Post ) {
            return get_post( $value->ID );
        }

        if ( is_numeric( $value ) ) {
            return get_post( (int) $value );
        }
    }

    return null;
}

function brackets_is_maintenance_enabled(): bool {
    if ( ! function_exists( 'get_field' ) ) {
        return false;
    }

    $toggle_fields = [
        'active-brackets',
        'active_brackets',
        'active-brackets-page',
        'active_brackets_page',
        'maintenance-mode',
        'maintenance_mode',
        'maintenance-active',
        'maintenance_active',
    ];

    $toggle_value = null;

    foreach ( $toggle_fields as $field_name ) {
        $value = get_field( $field_name, 'option' );

        if ( $value instanceof WP_Post || is_numeric( $value ) ) {
            continue;
        }

        if ( null !== $value ) {
            $toggle_value = $value;
            break;
        }
    }

    $maintenance_page = brackets_get_maintenance_page();

    if ( ! $maintenance_page instanceof WP_Post || 'publish' !== $maintenance_page->post_status ) {
        return false;
    }

    if ( null === $toggle_value ) {
        return false;
    }

    return filter_var( $toggle_value, FILTER_VALIDATE_BOOLEAN );
}

function brackets_should_handle_maintenance_request(): bool {
    if ( is_admin() ) {
        return false;
    }

    if ( is_user_logged_in() ) {
        return false;
    }

    if ( wp_doing_ajax() || wp_doing_cron() ) {
        return false;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return false;
    }

    if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
        return false;
    }

    if ( is_customize_preview() ) {
        return false;
    }

    $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    $request_path = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

    if ( in_array( $request_path, [ 'wp-login.php', 'robots.txt', 'favicon.ico' ], true ) ) {
        return false;
    }

    if ( str_starts_with( $request_path, 'wp-json' ) || str_starts_with( $request_path, 'wp-sitemap' ) ) {
        return false;
    }

    return true;
}

function brackets_set_maintenance_cache_headers(): void {
    if ( ! brackets_is_maintenance_enabled() || ! brackets_should_handle_maintenance_request() ) {
        return;
    }

    // Prevent page cache layers from storing the maintenance response.
    if ( ! defined( 'DONOTCACHEPAGE' ) ) {
        define( 'DONOTCACHEPAGE', true );
    }

    if ( ! defined( 'DONOTCACHEDB' ) ) {
        define( 'DONOTCACHEDB', true );
    }

    if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
        define( 'DONOTCACHEOBJECT', true );
    }

    if ( ! defined( 'DONOTMINIFY' ) ) {
        define( 'DONOTMINIFY', true );
    }

    if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
        define( 'DONOTROCKETOPTIMIZE', true );
    }
}
add_action( 'init', 'brackets_set_maintenance_cache_headers', 1 );

function brackets_get_maintenance_template( WP_Post $post ): string {
    $page_template = get_page_template_slug( $post );

    if ( $page_template ) {
        $template = locate_template( $page_template );

        if ( $template ) {
            return $template;
        }
    }

    $template = get_query_template( 'page' );

    if ( $template ) {
        return $template;
    }

    return locate_template( [ 'index.php' ] );
}

function brackets_render_maintenance_page(): void {
    if ( ! brackets_is_maintenance_enabled() || ! brackets_should_handle_maintenance_request() ) {
        return;
    }

    $maintenance_page = brackets_get_maintenance_page();

    if ( ! $maintenance_page instanceof WP_Post ) {
        return;
    }

    global $post, $wp_query, $wp_the_query, $wp;

    $post = $maintenance_page;

    if ( $wp_query instanceof WP_Query ) {
        $wp_query->posts             = [ $post ];
        $wp_query->post              = $post;
        $wp_query->post_count        = 1;
        $wp_query->found_posts       = 1;
        $wp_query->max_num_pages     = 1;
        $wp_query->queried_object    = $post;
        $wp_query->queried_object_id = $post->ID;
        $wp_query->is_page           = true;
        $wp_query->is_singular       = true;
        $wp_query->is_home           = false;
        $wp_query->is_front_page     = false;
        $wp_query->is_archive        = false;
        $wp_query->is_category       = false;
        $wp_query->is_tag            = false;
        $wp_query->is_tax            = false;
        $wp_query->is_search         = false;
        $wp_query->is_feed           = false;
        $wp_query->is_404            = false;
    }

    $wp_the_query = $wp_query;

    if ( isset( $wp ) ) {
        $wp->query_vars['page_id']  = $post->ID;
        $wp->query_vars['pagename'] = $post->post_name;
    }

    // Use a 503 status and no-cache headers so maintenance never sticks in cache.
    status_header( 503 );
    nocache_headers();
    header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true );
    header( 'Pragma: no-cache', true );
    header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT', true );
    header( 'Retry-After: 600', true );
    header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );

    setup_postdata( $post );

    $template = brackets_get_maintenance_template( $post );

    if ( ! $template ) {
        wp_reset_postdata();
        exit;
    }

    include $template;
    wp_reset_postdata();
    exit;
}
add_action( 'template_redirect', 'brackets_render_maintenance_page', 0 );
