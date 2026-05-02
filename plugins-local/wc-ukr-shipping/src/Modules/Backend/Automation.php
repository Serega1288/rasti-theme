<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Modules\Backend;

use kirillbdev\WCUkrShipping\Api\SmartyParcelWPApi;
use kirillbdev\WCUkrShipping\Component\Automation\Context;
use kirillbdev\WCUkrShipping\Component\Carriers\RozetkaDelivery\Label\BatchLabelRequestAdapter;
use kirillbdev\WCUkrShipping\Component\Carriers\RozetkaDelivery\Label\PurchaseLabelDataCollector;
use kirillbdev\WCUkrShipping\Component\Carriers\Ukrposhta\Label\UkrposhtaBatchLabelRequestBuilder;
use kirillbdev\WCUkrShipping\Component\SmartyParcel\OrderLabelRequestBuilder;
use kirillbdev\WCUkrShipping\Enums\CarrierSlug;
use kirillbdev\WCUkrShipping\Exceptions\SmartyParcel\SmartyParcelErrorException;
use kirillbdev\WCUkrShipping\Helpers\SmartyParcelHelper;
use kirillbdev\WCUkrShipping\Helpers\WCUSHelper;
use kirillbdev\WCUkrShipping\Services\AutomationService;
use kirillbdev\WCUkrShipping\Services\SmartyParcelService;
use kirillbdev\WCUSCore\Contracts\ModuleInterface;

class Automation implements ModuleInterface
{
    private AutomationService $automationService;
    private SmartyParcelService $smartyParcelService;
    private SmartyParcelWPApi $smartyParcelApi;

    public function __construct(
        AutomationService $automationService,
        SmartyParcelService $smartyParcelService,
        SmartyParcelWPApi $smartyParcelApi
    ) {
        $this->automationService = $automationService;
        $this->smartyParcelService = $smartyParcelService;
        $this->smartyParcelApi = $smartyParcelApi;
    }

    public function init(): void
    {
        add_action( 'woocommerce_order_status_changed', [$this, 'fireUpdateOrderAutomation'], 10, 3);
        add_action('wcus_smartyparcel_auto_create_label', [$this, 'autoCreateLabel']);
        add_action('wcus_smartyparcel_sync_order', [$this, 'syncOrder']);
    }

    public function fireUpdateOrderAutomation(int $orderId, string $fromStatus, string $toStatus): void
    {
        $order = wc_get_order($orderId);
        if (!$order || $order->get_type() !== 'shop_order') {
            return;
        }

        $this->automationService->executeEvent(
                AutomationService::EVENT_ORDER_STATUS_CHANGED,
            new Context(
                AutomationService::EVENT_ORDER_STATUS_CHANGED,
                wc_get_order($orderId),
                []
            )
        );
    }

    public function autoCreateLabel(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }

        /** @var \WC_Order $order */
        if ($this->smartyParcelService->getLabelByOrderId($order->get_id()) !== null) {
            return;
        }

        try {
            $carrierSlug = SmartyParcelHelper::getOrderCarrierSlug($order);
            $builder = null;
            switch ($carrierSlug) {
                case CarrierSlug::NOVA_POSHTA:
                    $builder = new OrderLabelRequestBuilder($order);
                    break;
                case CarrierSlug::UKRPOSHTA:
                    $builder = new UkrposhtaBatchLabelRequestBuilder($order);
                    break;
                case CarrierSlug::ROZETKA_DELIVERY:
                    $builder = new BatchLabelRequestAdapter(
                        new PurchaseLabelDataCollector($order)
                    );
                break;
            }

            if ($builder !== null) {
                $this->smartyParcelService->createLabel(
                    $carrierSlug,
                    $order->get_id(),
                    $builder
                );
            }
        } catch (SmartyParcelErrorException $e) {
            $order->add_meta_data(
                '_wcus_automation_error',
                sprintf(
                    '[Automation] Error creating label: Source: SmartyParcel | Error: [%d] %s',
                    $e->getCode(),
                    $e->getMessage()
                )
            );
            $order->save_meta_data();
        } catch (\Throwable $e) {
            $order->add_meta_data(
                '_wcus_automation_error',
                sprintf('[Automation] Error creating label: Source: Internal | Error: %s', $e->getMessage())
            );
            $order->save_meta_data();
        }
    }

    /**
     * todo: this is a mvp implementation, need to be refactored
     */
    public function syncOrder(int $orderId): void
    {
        if (!SmartyParcelHelper::isConnected()) {
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order) {
            return;
        }

        try {
            $orderShipping = WCUSHelper::getOrderShippingMethod($order);
            $billingOnly = 'billing_only' === get_option('woocommerce_ship_to_destination');
            $orderPayload = [
                'order_number' => $order->get_order_number(),
                'external_id' => (string)$order->get_id(),
                'currency' => $order->get_currency(),
                'source_status' => $order->get_status(),
                'order_total' => $order->get_total(),
                'shipping_total' => (float)$order->get_shipping_total(''),
                'tax_total' => $order->get_total_tax(),
                'discount_total' => $order->get_total_discount(),
                'subtotal' => $order->get_subtotal(),
                'shipping_carrier' => SmartyParcelHelper::getOrderCarrierSlug($order),
                'shipping_method_id' => $orderShipping ? $orderShipping->get_method_id() : null,
                'shipping_method_name' => $orderShipping ? $orderShipping->get_method_title() : null,
                'payment_method_id' => $order->get_payment_method(),
                'payment_method_name' => $order->get_payment_method_title(),
                'source_created_at' => $order->get_date_created() !== null
                    ? $order->get_date_created()->format('Y-m-d H:i:s')
                    : date('Y-m-d H:i:s'),
                'source_updated_at' => $order->get_date_modified() !== null
                    ? $order->get_date_modified()->format('Y-m-d H:i:s')
                    : date('Y-m-d H:i:s'),
                'to_address' => [
                    'name' => $billingOnly
                        ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                        : $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                    'email' => $order->get_billing_email(),
                    'phone' => $order->get_billing_phone(),
                    'country_code' => $billingOnly ? $order->get_billing_country() : $order->get_shipping_country(),
                    'pudo_point_id' => $this->getPudoPointId($orderShipping),
                    'city' => $billingOnly ? $order->get_billing_city() : $order->get_shipping_city(),
                    'state' => $billingOnly ? $order->get_billing_state() : $order->get_shipping_state(),
                    'address_1' => $billingOnly ? $order->get_billing_address_1() : $order->get_shipping_address_1(),
                    'address_2' => $billingOnly ? $order->get_billing_address_2() : $order->get_shipping_address_2(),
                    'postal_code' => $billingOnly ? $order->get_billing_postcode() : $order->get_shipping_postcode(),
                ],
                'line_items' => []
            ];

            // Collect line items
            /** @var \WC_Order_Item_Product $item */
            foreach ($order->get_items() as $item) {
                $orderPayload['line_items'][] = [
                    'description' => $item->get_name(),
                    'quantity' => $item->get_quantity(),
                    'price' => (float)$item->get_total() / $item->get_quantity(),
                    'currency' => $order->get_currency(),
                    'weight' => (float)$item->get_product()->get_weight(),
                    'weight_unit' => get_option('woocommerce_weight_unit', 'kg'),
                    'sku' => $item->get_product()->get_sku(),
                ];
            }

            $response = $this->smartyParcelApi->sendRequest('/v1/orders', $orderPayload);

            $order->add_meta_data('_smartyparcel_order_id', $response['id']);
            $order->save_meta_data();
        } catch (\Throwable $e) {
            // Do nothing yet
        }
    }

    private function getPudoPointId(\WC_Order_Item_Shipping $orderShipping): ?string
    {
        switch ($orderShipping->get_method_id()) {
            case WC_UKR_SHIPPING_NP_SHIPPING_NAME:
                return $orderShipping->get_meta('wcus_warehouse_ref');
            case WCUS_SHIPPING_METHOD_UKRPOSHTA:
                return $orderShipping->get_meta('wcus_ukrposhta_warehouse_id');
            case WCUS_SHIPPING_METHOD_ROZETKA:
                return $orderShipping->get_meta('wcus_rozetka_warehouse_id');
            case WCUS_SHIPPING_METHOD_NOVA_POST:
                return $orderShipping->get_meta('wcus_nova_post_warehouse_id');
            case WCUS_SHIPPING_METHOD_MEEST:
                return $orderShipping->get_meta('wcus_pudo_point_id');
        }

        return null;
    }
}
