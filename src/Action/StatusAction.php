<?php

namespace PayumTW\Collect\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['ret'] === 'OK') {
            $request->markCaptured();

            return;
        }

        if ($details['ret'] === 'FAIL') {
            $request->markFailed();

            return;
        }

        if (isset($details['link_id']) === true) {
            if ($details['status'] === 'OK') {
                if (isset($details['refund_amount']) === true) {
                    $request->markRefunded();

                    return;
                }

                if (isset($details['order_detail']) === false) {
                    $request->markCanceled();

                    return;
                }
            }
        }

        $statusMap = [
            // B 授權完成
            'B' => 'markCaptured',
            // O 請款作業中(請款作業中，無法進行取消授權)
            'O' => 'markSuspended',
            // E 請款完成
            'E' => 'markCaptured',
            // F 授權失敗
            'F' => 'markFailed',
            // D 訂單逾期
            'D' => 'markExpired',
            // P 請款失敗
            'P' => 'markFailed',
            // M 取消交易完成
            'M' => 'markCanceled',
            // N 取消交易失敗
            'N' => 'markFailed',
            // Q 取消授權完成
            'Q' => 'markRefunded',
            // R 取消授權失敗
            'R' => 'markFailed',

            // CVS
            'OK' => 'markCaptured',
            'ERROR' => 'markFailed',
        ];

        if (isset($statusMap[$details['status']]) === true) {
            call_user_func([$request, $statusMap[$details['status']]]);

            return;
        }

        $request->markNew();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
