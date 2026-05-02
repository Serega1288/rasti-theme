<?php

function project_theme_parse_daily_lock_timestamp( string $time_value, DateTimeImmutable $reference ): ?DateTimeImmutable {
    $time_value = trim( $time_value );

    if ( '' === $time_value ) {
        return null;
    }

    $parts = explode( ':', $time_value );

    if ( count( $parts ) < 2 ) {
        return null;
    }

    $hours   = (int) $parts[0];
    $minutes = (int) $parts[1];

    if ( $hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59 ) {
        return null;
    }

    return $reference->setTime( $hours, $minutes, 0 );
}

function project_theme_get_site_lock_config(): array {
    $default_config = array(
        'enabled'         => false,
        'serverNowMs'     => time() * 1000,
        'repeatIntervalMs'=> DAY_IN_SECONDS * 1000,
        'timers'          => array(),
    );

    if ( ! function_exists( 'get_field' ) ) {
        return $default_config;
    }

    $timezone         = wp_timezone();
    $now_timestamp    = time();
    $now_local_string = wp_date( 'Y-m-d H:i:s', $now_timestamp, $timezone );
    $now_local        = new DateTimeImmutable( $now_local_string, $timezone );
    $timers           = array();
    $rows             = get_field( 'site_lock_items', 'option' );

    $is_enabled_value = get_field( 'site_lock_enabled', 'option' );
    $has_global_toggle = null !== $is_enabled_value && '' !== $is_enabled_value;

    if ( $has_global_toggle && ! (bool) $is_enabled_value ) {
        return $default_config;
    }

    if ( ! is_array( $rows ) ) {
        return $default_config;
    }

    foreach ( $rows as $index => $row ) {
        if ( empty( $row['is_active'] ) ) {
            continue;
        }

        $start_time = isset( $row['start_datetime'] ) ? (string) $row['start_datetime'] : '';
        $duration   = isset( $row['duration_seconds'] ) ? (int) $row['duration_seconds'] : 0;

        if ( '' === $start_time || $duration < 1 ) {
            continue;
        }

        $today_start = project_theme_parse_daily_lock_timestamp( $start_time, $now_local );

        if ( ! $today_start ) {
            continue;
        }

        $active_end  = $today_start->modify( '+' . $duration . ' seconds' );
        $tomorrow    = $today_start->modify( '+1 day' );
        $is_active   = $now_local >= $today_start && $now_local < $active_end;
        $next_start  = $is_active ? $tomorrow : ( $now_local < $today_start ? $today_start : $tomorrow );
        $active_start = $is_active ? $today_start : null;

        $timers[] = array(
            'id'              => 'site-lock-' . $index,
            'durationSeconds' => $duration,
            'activeStartMs'   => $active_start ? $active_start->getTimestamp() * 1000 : 0,
            'nextStartMs'     => $next_start->getTimestamp() * 1000,
            'title'           => isset( $row['title'] ) ? wp_strip_all_tags( (string) $row['title'] ) : '',
            'titleSub'        => isset( $row['title_sub'] ) ? trim( wp_kses_post( (string) $row['title_sub'] ) ) : '',
            'message'         => isset( $row['desc'] ) ? trim( wp_kses_post( (string) $row['desc'] ) ) : '',
        );
    }

    if ( empty( $timers ) ) {
        return $default_config;
    }

    return array(
        'enabled'          => true,
        'serverNowMs'      => $now_timestamp * 1000,
        'repeatIntervalMs' => DAY_IN_SECONDS * 1000,
        'timers'           => array_values( $timers ),
    );
}

function project_theme_render_site_lock_overlay(): void {
    $config = project_theme_get_site_lock_config();
    $is_active_now = project_theme_is_site_lock_active_now();

    if ( empty( $config['enabled'] ) ) {
        return;
    }
    ?>
    <?php if ( $is_active_now ) : ?>
        <script>
            (function () {
                var applySiteLockState = function () {
                    document.documentElement.classList.add('site-lock-active');
                    document.body.classList.add('site-lock-active', 'ovh');
                    document.body.style.overflow = 'hidden';
                };

                applySiteLockState();
                document.addEventListener('DOMContentLoaded', applySiteLockState, { once: true });
            })();
        </script>
    <?php endif; ?>

    <div class="site-lock-overlay d-flex align-items-center justify-content-center" data-site-lock-overlay<?php echo $is_active_now ? '' : ' hidden'; ?> aria-hidden="<?php echo $is_active_now ? 'false' : 'true'; ?>">
        <div class="backdrop"></div>
        <div class="panel text-center" role="dialog" aria-modal="true" aria-labelledby="site-lock-title">
            <h2 class="title t-green ttu" id="site-lock-title" data-site-lock-title hidden></h2>
            <div class="title-sub t-green ts-20 ttu" data-site-lock-title-sub hidden></div>
            <div class="message t-white ts-20  ts-sm-16" data-site-lock-message hidden></div>
            <div class="preloader-counter"> 
                <span class="preloader-bracket preloader-bracket-1"></span>
                <span class="preloader-dash">
                    <div class="timer" data-site-lock-timer>00:00</div>
                </span>
                <span class="preloader-bracket preloader-bracket-2"></span>
            </div>
            <span class="preloader-bracket preloader-bracket-2"></span>
        </div>
    </div>

    <?php
}
add_action( 'wp_body_open', 'project_theme_render_site_lock_overlay', 20 );

function project_theme_is_site_lock_active_now(): bool {
    $config = project_theme_get_site_lock_config();

    if ( empty( $config['enabled'] ) || empty( $config['timers'] ) || ! is_array( $config['timers'] ) ) {
        return false;
    }

    foreach ( $config['timers'] as $timer ) {
        if ( ! empty( $timer['activeStartMs'] ) ) {
            return true;
        }
    }

    return false;
}

add_filter( 'body_class', 'project_theme_add_site_lock_body_class' );
function project_theme_add_site_lock_body_class( array $classes ): array {
    if ( project_theme_is_site_lock_active_now() ) {
        if ( ! in_array( 'ovh', $classes, true ) ) {
            $classes[] = 'ovh';
        }

        if ( ! in_array( 'site-lock-active', $classes, true ) ) {
            $classes[] = 'site-lock-active';
        }
    }

    return $classes;
}
