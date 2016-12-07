<?php

namespace PayumTW\Collect\Action;

use PayumTW\Collect\Api;
use Payum\Core\Request\Notify;
use Payum\Core\Request\GetToken;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyNullAction implements ActionInterface, GatewayAwareInterface
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
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $notifyToken = md5($httpRequest->request['order_no']);
        $getToken = new GetToken($notifyToken);
        $this->gateway->execute($getToken);
        $this->gateway->execute(new Notify($getToken->getToken()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            null === $request->getModel();
    }
}
