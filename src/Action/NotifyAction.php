<?php

namespace PayumTW\Collect\Action;

use Payum\Core\Request\Sync;
use Payum\Core\Request\Notify;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new Sync($details));

        if ($details['status'] === '-1') {
            throw new HttpResponse('FAIL', 400, ['Content-Type' => 'text/plain']);
        }

        throw new HttpResponse('OK', 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
