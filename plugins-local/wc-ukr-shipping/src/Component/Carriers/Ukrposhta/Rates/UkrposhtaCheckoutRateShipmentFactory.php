<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Component\Carriers\Ukrposhta\Rates;

use kirillbdev\WCUkrShipping\Enums\CarrierSlug;
use kirillbdev\WCUkrShipping\Factories\Rates\CheckoutRateShipmentFactory;
use kirillbdev\WCUkrShipping\Helpers\WCUSHelper;

class UkrposhtaCheckoutRateShipmentFactory extends CheckoutRateShipmentFactory
{
    protected bool $useDimensions = false;
    private string $serviceType;
    private string $deliveryType;

    public function __construct(string $serviceType, string $deliveryType)
    {
        parent::__construct(CarrierSlug::UKRPOSHTA);
        $this->serviceType = $serviceType;
        $this->deliveryType = $deliveryType;
    }

    protected function isFull(): bool
    {
        if ($this->deliveryType === 'door') {
            return !empty($this->get('ship_to')['postal_code'] ?? null);
        }
        return !empty($this->getShipToCarrierCityId());
    }

    protected function getShipToCarrierCityId(): ?string
    {
        return $this->get("wcus_ukrposhta_{$this->fieldGroup}_city", '');
    }

    protected function getShipFromCarrierCityId(): ?string
    {
        $city = WCUSHelper::safeGetJsonOption('wcus_ukrposhta_sender_city');
        return $city['value'] ?? '';
    }

    protected function getShipToPostalCode(): ?string
    {
        return $this->deliveryType === 'door'
            ? ($this->get('ship_to')['postal_code'] ?? null)
            : null;
    }

    protected function getServiceType(): ?string
    {
        return 'ukrposhta_' . $this->serviceType;
    }

    protected function getDeliveryType(): string
    {
        return $this->deliveryType === 'door' ? 'w2d' : 'w2w';
    }
}
