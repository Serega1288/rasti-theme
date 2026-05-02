<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Factories\Rates\NovaPoshta;

use kirillbdev\WCUkrShipping\Enums\CarrierSlug;
use kirillbdev\WCUkrShipping\Factories\Rates\CheckoutRateShipmentFactory;

class NovaPoshtaCheckoutRateShipmentFactory extends CheckoutRateShipmentFactory
{
    protected bool $useDimensions = false;

    public function __construct()
    {
        parent::__construct(CarrierSlug::NOVA_POSHTA);
    }

    protected function getShipToCarrierCityId(): ?string
    {
        if ($this->get('shipping_type') === 'doors') {
            return $this->get("wcus_np_{$this->fieldGroup}_settlement_ref");
        }

        return $this->get("wcus_np_{$this->fieldGroup}_city");
    }

    protected function getShipFromCarrierCityId(): ?string
    {
        return (string)wc_ukr_shipping_get_option('wc_ukr_shipping_np_sender_city');
    }

    protected function getDeliveryType(): string
    {
        switch ($this->get('shipping_type')) {
            case 'doors':
                return 'w2d';
            case 'poshtomat':
                return 'w2l';
            default:
                return 'w2w';
        }
    }

    protected function isFull(): bool
    {
        return !empty($this->getShipToCarrierCityId());
    }
}
