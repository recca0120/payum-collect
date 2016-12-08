<?php

use Mockery as m;
use PayumTW\Collect\Action\NotifyNullAction;

class NotifyNullActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_refund()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');

        $details = null;

        $apiId = 'CC0000000001';
        $transId = '550e8400e29b41d4a716446655440000';
        $orderNo = 'PO5488277';
        $amount = 1250;
        $status = 'B';
        $paymentCode = 1;
        $paymentDetail = [
            'auth_code' => '123456',
            'auth_card_no' => '0000',
        ];
        $memo = [];
        $expireTime = '2013-09-28T08:15:00+08:00';
        $createTime = '2013-09-28T08:00:00+08:00';
        $modifyTime = '2013-09-28T08:30:00+08:00';
        $nonce = '1234569999';

        $checksum = md5($apiId.':'.$transId.':'.$amount.':'.$status.':'.$nonce);

        $returnValue = [
            'api_id' => $apiId,
            'trans_id' => $transId,
            'order_no' => $orderNo,
            'amount' => $amount,
            'status' => $status,
            'payment_code' => $paymentCode,
            'payment_detail' => $paymentDetail,
            'memo' => $memo,
            'expire_time' => $expireTime,
            'create_time' => $createTime,
            'modify_time' => $modifyTime,
            'nonce' => $nonce,
            'checksum' => $checksum,
        ];

        $notifyHash = null;

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($returnValue) {
                $getHttpRequest->request = $returnValue;
            })->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetToken'))->andReturnUsing(function ($getToken) use (&$notifyHash) {
                $notifyHash = $getToken->getHash();
            });

        $action = new NotifyNullAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($notifyHash, md5($returnValue['order_no']));
        $request->shouldHaveReceived('getModel')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetToken'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Notify'))->once();
    }
}
