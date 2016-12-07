<?php

namespace PayumTW\Collect;

use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\SyncAction;
use PayumTW\Collect\Action\CancelAction;
use PayumTW\Collect\Action\RefundAction;
use PayumTW\Collect\Action\StatusAction;
use PayumTW\Collect\Action\CaptureAction;
use PayumTW\Collect\Action\ConvertPaymentAction;
use PayumTW\Collect\Action\Api\CancelTransactionAction;
use PayumTW\Collect\Action\Api\CreateTransactionAction;
use PayumTW\Collect\Action\Api\RefundTransactionAction;
use PayumTW\Collect\Action\Api\GetTransactionDataAction;

class CollectGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'collect',
            'payum.factory_title' => 'Collect',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.refund_transaction' => new RefundTransactionAction(),
            'payum.action.api.cancel_transaction' => new CancelTransactionAction(),
            'payum.action.api.get_transaction_data' => new GetTransactionDataAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'link_id' => null,
                'hash_base' => null,
                'sandbox' => false,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['link_id', 'hash_base'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
