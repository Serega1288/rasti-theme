<?php
    if ( ! defined('ABSPATH')) {
        exit;
    }

    use \kirillbdev\WCUkrShipping\Helpers\HtmlHelper;
?>

<div id="wcus-pane-shipment" class="wcus-tab-pane active">

    <div class="wcus-message wcus-message--warning wcus-mb-2">
        <?php esc_html_e('The plugin must be connected to the Smarty Parcel service to use the functionality of creating a TTN', 'wc-ukr-shipping-i18n'); ?>
        <a href="https://smartyparcel.com/docs/wcus-smarty-parcel-connect/" target="_blank"><?php esc_html_e('Documentation', 'wc-ukr-shipping-i18n'); ?></a>
    </div>

    <?php
        $ukrPoshtaAccounts = [];
        foreach ($carrierAccounts as $account) {
            if (($account['carrier_slug'] ?? '') === 'rozetka_delivery') {
                $ukrPoshtaAccounts[$account['id']] = $account['name'];;
            }
        }

        HtmlHelper::selectField(
            'wcus[rozetka_default_carrier]',
            __('Default carrier account', 'wc-ukr-shipping-i18n'),
            $ukrPoshtaAccounts,
            wc_ukr_shipping_get_option('wcus_rozetka_default_carrier')
        );

        HtmlHelper::selectField(
            'wcus[rozetka_ttn_default_payer]',
            __('Delivery payer', 'wc-ukr-shipping-i18n'),
            [
                'sender' => __('Sender', 'wc-ukr-shipping-i18n'),
                'recipient' => __('Recipient', 'wc-ukr-shipping-i18n'),
            ],
            wc_ukr_shipping_get_option('wcus_rozetka_ttn_default_payer')
        );
    ?>

</div>
