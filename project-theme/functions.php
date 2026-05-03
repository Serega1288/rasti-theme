<?php
// підключення лінків
require_once get_stylesheet_directory() . '/inc/enqueue.php';
// всі скрипти теми
require_once get_stylesheet_directory() . '/inc/theme-admin.php';
// розміри картинок
require_once get_stylesheet_directory() . '/inc/image.php';
// плагін ACF
require_once get_stylesheet_directory() . '/inc/acf.php';
// глобальний lock-screen з таймером
require_once get_stylesheet_directory() . '/inc/site-lock.php';
// вукомерс
require_once get_stylesheet_directory() . '/inc/woo.php';
// підтвердження оплати — email при статусі "в обробці"
add_filter( 'woocommerce_email_classes', function ( array $email_classes ): array {
    require_once get_stylesheet_directory() . '/inc/class-payment-confirmation-email.php';
    $email_classes['Project_Theme_Payment_Confirmation_Email'] = new Project_Theme_Payment_Confirmation_Email();
    return $email_classes;
} );

add_action( 'woocommerce_order_status_changed', function ( int $order_id, string $old_status, string $new_status, WC_Order $order ): void {
    if ( 'processing' !== $new_status || ! class_exists( 'WC_Email' ) ) {
        return;
    }
    require_once get_stylesheet_directory() . '/inc/class-payment-confirmation-email.php';
    ( new Project_Theme_Payment_Confirmation_Email() )->trigger( $order_id, $order );
}, 20, 4 );
// режим заглушки
require_once get_stylesheet_directory() . '/brackets.php';
