<?php

namespace PayumTW\Collect\Action;

use Payum\Core\Request\Convert;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class ConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['cust_order_no'] = $payment->getNumber();
        $details['order_amount'] = $payment->getTotalAmount();
        $details['order_detail'] = $payment->getDescription();

        // CVS
        $details['payer_email'] = $payment->getClientEmail();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array';
    }
}
