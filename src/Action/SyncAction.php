<?php

namespace PayumTW\Collect\Action;

use Payum\Core\Request\Sync;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Request\Api\GetTransactionData;
use Payum\Core\Exception\RequestNotSupportedException;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new GetTransactionData($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
