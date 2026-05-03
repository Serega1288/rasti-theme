<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Project_Theme_Payment_Confirmation_Email extends WC_Email {

    public function __construct() {
        $this->id             = 'project_theme_payment_confirmation';
        $this->customer_email = true;
        $this->title          = __( 'Підтвердження оплати', 'project-theme' );
        $this->description    = __( 'Надсилається покупцю коли замовлення переходить у статус "В обробці".', 'project-theme' );
        $this->template_html  = 'emails/payment-confirmation.php';
        $this->placeholders   = [
            '{order_date}'   => '',
            '{order_number}' => '',
        ];

        parent::__construct();
    }

    public function trigger( int $order_id, WC_Order $order = null ): void {
        $this->setup_locale();

        if ( ! $order ) {
            $order = wc_get_order( $order_id );
        }

        if ( ! $order instanceof WC_Order ) {
            return;
        }

        $this->object                         = $order;
        $this->recipient                      = $order->get_billing_email();
        $this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
        $this->placeholders['{order_number}'] = $order->get_order_number();

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    public function get_content_html(): string {
        $template = get_stylesheet_directory() . '/woocommerce/' . $this->template_html;

        if ( ! file_exists( $template ) ) {
            return '';
        }

        $order         = $this->object;
        $email_heading = $this->get_heading();
        $sent_to_admin = false;
        $plain_text    = false;
        $email         = $this;

        ob_start();
        include $template;
        return (string) ob_get_clean();
    }

    public function get_content_plain(): string {
        return '';
    }

    public function get_default_subject(): string {
        return __( 'Дякуємо за підтримку! Оплата отримана', 'project-theme' );
    }

    public function get_default_heading(): string {
        return __( 'Дякуємо за замовлення!', 'project-theme' );
    }
}
