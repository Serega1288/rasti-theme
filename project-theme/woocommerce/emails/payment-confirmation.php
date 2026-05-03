<?php
/**
 * Шаблон листа підтвердження оплати.
 *
 * Доступні змінні:
 *   $order         — об'єкт WC_Order
 *   $email_heading — заголовок листа (з налаштувань WC)
 *   $email         — об'єкт email класу
 *
 * Корисні методи $order:
 *   $order->get_id()
 *   $order->get_billing_first_name()
 *   $order->get_billing_last_name()
 *   $order->get_billing_email()
 *   $order->get_formatted_order_total()
 *   $order->get_date_paid()->date_i18n( get_option('date_format') )
 *   $order->get_items()  — товари замовлення
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
</head>
<body>

    <!-- =============================================
         СЮДИ ВСТАВЛЯЙ СВОЮ ВЕРСТКУ ЛИСТА
         ============================================= -->

    <?php if ( $email_heading ) : ?>
        <h1><?php echo esc_html( $email_heading ); ?></h1>
    <?php endif; ?>

    <p>
        Привіт, <?php echo esc_html( $order->get_billing_first_name() ); ?>!<br>
        Твоя оплата для замовлення №<?php echo esc_html( $order->get_id() ); ?> отримана.
    </p>

    <!-- =============================================
         КІНЕЦЬ ВЕРСТКИ
         ============================================= -->

</body>
</html>
