<?php

function project_theme_render_header_cart_count(): void {
    $cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
    $cart_label = $cart_count > 0 ? sprintf( '%02d', $cart_count ) : '';
    ?>
    <li class="cart-count">
        <a count="<?php echo $cart_count; ?>" class="brackets" href="<?php echo esc_url( $cart_url ); ?>">
            Cart
            <?php if ( $cart_count > 0 ) : ?>
                <span><?php echo esc_html( $cart_label ); ?></span>
            <?php endif; ?>
        </a>
    </li>
    <?php
}

add_filter( 'woocommerce_add_to_cart_fragments', 'project_theme_header_cart_count_fragment' );
function project_theme_header_cart_count_fragment( array $fragments ): array {
    ob_start();
    project_theme_render_header_cart_count();
    $fragments['li.cart-count'] = ob_get_clean();

    return $fragments;
}
