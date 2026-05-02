<?php
/**
 * Empty cart page
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$catalog_url = home_url( '/#go-catalog' );
?>

<div class="wc-empty-cart-message">
    <?php wc_print_notice( esc_html__( 'Your cart is currently empty.', 'woocommerce' ), 'notice' ); ?>
</div>

<p class="return-to-shop">
        <a class="btn btn-4" href="<?php echo esc_url( $catalog_url ); ?>">
            <?php esc_html_e( 'Перейти в каталог', 'project-theme' ); ?>
        </a>
    </p>
