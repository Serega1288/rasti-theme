<?php

namespace kirillbdev\WCUkrShipping\Modules\Backend;

use kirillbdev\WCUSCore\Contracts\ModuleInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

class ShippingItemDrawer implements ModuleInterface
{
    /**
     * Boot function
     */
    public function init()
    {
        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'hideShippingMeta']);
    }

    public function hideShippingMeta(array $keys): array
    {
        $keys[] = 'wcus_settlement_name';
        $keys[] = 'wcus_settlement_area';
        $keys[] = 'wcus_settlement_full';
        $keys[] = 'wcus_street_name';
        $keys[] = 'wcus_street_full';
        $keys[] = 'wcus_api_address';
        $keys[] = 'wcus_settlement_region';
        $keys[] = 'wcus_area_ref';

        return $keys;
    }
}
