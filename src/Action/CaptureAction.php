<?php

namespace PayumTW\Collect\Action;

use Payum\Core\Request\Capture;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Collect\Action\Api\BaseApiAwareAction;
use PayumTW\Collect\Request\Api\CreateTransaction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

class CaptureAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->request['ret']) === true) {
            if ($this->api->verifyHash($httpRequest->request) === false) {
                $httpRequest->request['ret'] = 'FAIL';
            }
            $details->replace($httpRequest->request);

            return;
        }

        // CVS
        if (isset($httpRequest->request['status']) === true) {
            if ($this->api->verifyHash($httpRequest->request) === false) {
                $httpRequest->request['status'] = 'ERROR';
            }
            $details->replace($httpRequest->request);

            return;
        }

        $token = $request->getToken();
        $targetUrl = rtrim($token->getTargetUrl(), '/');
        $targetUrl = substr($targetUrl, 0, strrpos($targetUrl, '/'));
        $token->setTargetUrl($targetUrl);
        $token->save();

        $notifyToken = $this->tokenFactory->createNotifyToken(
            $token->getGatewayName(),
            $token->getDetails()
        );

        $notifyToken->setHash(md5($details['cust_order_no']));
        $notifyToken->save();

        $this->gateway->execute(new CreateTransaction($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
