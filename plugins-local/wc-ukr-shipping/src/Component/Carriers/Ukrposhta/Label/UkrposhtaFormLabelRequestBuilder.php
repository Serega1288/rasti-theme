<?php

declare(strict_types=1);

namespace kirillbdev\WCUkrShipping\Component\Carriers\Ukrposhta\Label;

use kirillbdev\WCUkrShipping\Component\SmartyParcel\LabelRequestBuilderInterface;
use kirillbdev\WCUkrShipping\Helpers\WCUSHelper;
use kirillbdev\WCUSCore\Http\Request;

class UkrposhtaFormLabelRequestBuilder implements LabelRequestBuilderInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function build(): array
    {
        $request = $this->request;
        $recipient = $request->get('recipient');
        $sender = $request->get('sender');
        $shipTo = [
            'name' => sprintf(
                '%s %s%s',
                WCUSHelper::prepareApiString($recipient['first_name']),
                WCUSHelper::prepareApiString($recipient['last_name']),
                $recipient['middle_name']
                    ? ' ' . WCUSHelper::prepareApiString($recipient['middle_name'])
                    : '',
            ),
            'phone' => WCUSHelper::preparePhone($recipient['phone']),
            'country_code' => 'UA',
        ];
        if (($recipient['delivery_type'] ?? '') === 'door') {
            $shipTo = array_merge($shipTo, $recipient['ship_to']);
            unset($shipTo['address_full']);
        } else {
            $shipTo['pudo_point_id'] = $recipient['warehouse']['value'];
        }

        // Sender
        $shipFrom = [
            'phone' => WCUSHelper::preparePhone($sender['phone']),
            'email' => $sender['email'],
            'pudo_point_id' => $sender['warehouse']['value'],
            'country_code' => 'UA',
        ];

        if ($sender['type'] === 'individual') {
            $shipFrom['name'] = sprintf(
                '%s %s%s',
                WCUSHelper::prepareApiString($sender['first_name']),
                WCUSHelper::prepareApiString($sender['last_name']),
                $sender['middle_name']
                    ? ' ' . WCUSHelper::prepareApiString($sender['middle_name'])
                    : '',
            );
        } else {
            $shipFrom['name'] = $sender['company_name'];
            $shipFrom['tax_ids'] = [
                [
                    'type' => 'tin',
                    'number' => $sender['tin'],
                    'country' => 'UA',
                ]
            ];
        }

        $labelRequest = [
            'carrier_account_id' => $request->get('sender')['carrier_account_id'],
            'service_type' => $request->get('common')['service_type'],
            'billing' => [
                'paid_by' => $request->get('common')['paid_by'],
                'payment_method' => 'cash',
            ],
            'shipment' => [
                'ship_date' => date('Y-m-d'),
                'ship_from' => $shipFrom,
                'ship_to' => $shipTo,
            ]
        ];

        // Parcels
        $parcels = [];
        foreach ($request->get('common')['parcels'] as $index => $item) {
            $parcels[] = [
                'declared_value' => [
                    'amount' => (float)$request->get('common')['declared_price'],
                    'currency' => 'UAH',
                ],
                'weight' => [
                    'value' => (float)$item['weight'],
                    'unit' => 'kg',
                ],
                'dimensions' => [
                    'width' => (int)$item['width'],
                    'height' => (int)$item['height'],
                    'length' => (int)$item['length'],
                    'unit' => 'cm',
                ],
                'description' => $request->get('common')['description'],
            ];
        }
        $labelRequest['shipment']['parcels'] = $parcels;
        $labelRequest['shipment']['external_order_id'] =  $request->get('common')['external_order_id'];

        // Service options
        $labelRequest['service_options'] = [
            'ukrposhta_on_fail_receive' => $request->get('additional_services')['on_fail_receive'],
            'ukrposhta_check_on_delivery' => $request->get('additional_services')['check_on_delivery'] === "true",
            'ukrposhta_sms_notification' => $request->get('additional_services')['sms_notification'] === "true",
        ];

        // COD
        if ($request->get('cod')['active'] !== 'false') {
            $labelRequest['service_options']['cod'] = [
                'payment_method' => 'cash',
                'paid_by' => $request->get('cod')['paid_by'],
                'value' => [
                    'amount' => (float)$request->get('cod')['amount'],
                    'currency' => 'UAH',
                ]
            ];
            if ($sender['type'] === 'private_entrepreneur') {
                $labelRequest['service_options']['cod']['payment_method'] = 'cash_equivalent';
                $labelRequest['service_options']['cod']['recipient_iban'] = $sender['iban'];
            }
        }

        return $labelRequest;
    }
}
