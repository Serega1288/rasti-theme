<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Component\SmartyParcel;

use kirillbdev\WCUkrShipping\Helpers\WCUSHelper;

class ProxyLabelRequestBuilder implements LabelRequestBuilderInterface
{
    private array $labelRequest;

    public function __construct(array $labelRequest)
    {
        $this->labelRequest = $labelRequest;
    }

    public function build(): array
    {
        return $this->getNormalizedRequest();
    }

    private function getNormalizedRequest(): array
    {
        $request = $this->labelRequest;
        if (isset($request['shipment']['ship_from']['name'])) {
            $request['shipment']['ship_from']['name'] = WCUSHelper::prepareApiString($request['shipment']['ship_from']['name']);
        }
        if (isset($request['shipment']['ship_to']['name'])) {
            $request['shipment']['ship_to']['name'] = WCUSHelper::prepareApiString($request['shipment']['ship_to']['name']);
        }

        return $request;
    }
}
