<?php

namespace PayumTW\Collect\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Request\Api\RefundTransaction;
use Payum\Core\Exception\RequestNotSupportedException;

class RefundTransactionAction extends BaseApiAwareAction
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

        $details->validateNotEmpty(['cust_order_no', 'order_amount', 'refund_amount']);

        $details->replace($this->api->refundTransaction((array) $details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RefundTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
