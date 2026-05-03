<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

class Project_Theme_Payment_Confirmation_Email extends WC_Email {

    public function __construct() {
        $this->id             = 'payment_confirmation';
        $this->customer_email = true;
        $this->title          = __( 'Підтвердження оплати', 'project-theme' );
        $this->description    = __( 'Лист клієнту після успішної оплати замовлення.', 'project-theme' );
        $this->template_html  = 'emails/payment-confirmation.php';
        $this->template_base  = get_stylesheet_directory() . '/woocommerce/';

        $this->subject = $this->get_option(
            'subject',
            __( 'Дякуємо за підтримку! Оплата отримана', 'project-theme' )
        );
        $this->heading = $this->get_option( 'heading', '' );

        add_action( 'woocommerce_order_status_changed', function( $id, $from, $to ) {
            error_log( "[payment-confirmation] order_status_changed #{$id}: {$from} -> {$to}" );
            if ( in_array( $to, [ 'processing', 'completed' ], true ) ) {
                $this->trigger( (int) $id );
            }
        }, 10, 3 );

        parent::__construct();
    }

    public function trigger( int $order_id ): void {
        $log = wc_get_logger();
        $ctx = [ 'source' => 'payment-confirmation-email' ];

        $log->info( "trigger() called for order #{$order_id}", $ctx );

        if ( ! $order_id ) {
            $log->warning( 'No order_id', $ctx );
            return;
        }

        $this->object = wc_get_order( $order_id );

        if ( ! $this->object ) {
            $log->warning( "Order #{$order_id} not found", $ctx );
            return;
        }

        if ( $this->object->get_meta( '_payment_confirmation_email_sent' ) ) {
            $log->info( "Email already sent for order #{$order_id}, skipping", $ctx );
            return;
        }

        $this->recipient = $this->object->get_billing_email();

        if ( ! $this->is_enabled() ) {
            $log->warning( 'Email is disabled in WC settings', $ctx );
            return;
        }

        if ( ! $this->get_recipient() ) {
            $log->warning( "No recipient for order #{$order_id}", $ctx );
            return;
        }

        $log->info( "Sending to {$this->recipient} for order #{$order_id}", $ctx );

        $this->object->update_meta_data( '_payment_confirmation_email_sent', '1' );
        $this->object->save();

        $result = $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );

        $log->info( 'send() result: ' . ( $result ? 'true' : 'false' ), $ctx );
    }

    public function get_content_html(): string {
        return wc_get_template_html(
            $this->template_html,
            [
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'email'         => $this,
            ],
            '',
            $this->template_base
        );
    }

    // Текстова версія не потрібна, але WC_Email вимагає метод
    public function get_content_plain(): string {
        return '';
    }

    public function init_form_fields(): void {
        $this->form_fields = [
            'enabled' => [
                'title'   => __( 'Увімкнено', 'project-theme' ),
                'type'    => 'checkbox',
                'label'   => __( 'Активувати цей email', 'project-theme' ),
                'default' => 'yes',
            ],
            'subject' => [
                'title'       => __( 'Тема листа', 'project-theme' ),
                'type'        => 'text',
                'description' => __( 'Тема email повідомлення.', 'project-theme' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
                'desc_tip'    => true,
            ],
            'heading' => [
                'title'       => __( 'Заголовок листа', 'project-theme' ),
                'type'        => 'text',
                'description' => __( 'Заголовок всередині листа (необов\'язково).', 'project-theme' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ],
        ];
    }

    public function get_default_subject(): string {
        return __( 'Дякуємо за підтримку! Оплата отримана', 'project-theme' );
    }
}
