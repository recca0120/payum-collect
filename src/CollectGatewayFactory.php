<?php

namespace PayumTW\Collect;

use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\CancelAction;
use PayumTW\Collect\Action\NotifyAction;
use PayumTW\Collect\Action\RefundAction;
use PayumTW\Collect\Action\StatusAction;
use PayumTW\Collect\Action\CaptureAction;
use PayumTW\Collect\Action\NotifyNullAction;
use PayumTW\Collect\Action\ConvertPaymentAction;
use PayumTW\Collect\Action\Api\CancelTransactionAction;
use PayumTW\Collect\Action\Api\CreateTransactionAction;
use PayumTW\Collect\Action\Api\RefundTransactionAction;

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
            'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.refund_transaction' => new RefundTransactionAction(),
            'payum.action.api.cancel_transaction' => new CancelTransactionAction(),
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

                return new CollectApi((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
