<?php

namespace PayumTW\Collect\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Request\Api\CancelTransaction;
use Payum\Core\Exception\RequestNotSupportedException;

class CancelTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request RefundTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty(['cust_order_no', 'order_amount']);

        $details->replace($this->api->cancelTransaction((array) $details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CancelTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
