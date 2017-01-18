<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\NotifyAction;

class NotifyActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_notify_success()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Collect\Api');

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

        $response = [
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

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response)->andReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(200, $e->getStatusCode());
            $this->assertSame('OK', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }

    public function test_notify_when_checksum_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Collect\Api');

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

        $checksum = 'A'.md5($apiId.':'.$transId.':'.$amount.':'.$status.':'.$nonce);

        $response = [
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

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('verifyHash')->with($response)->andReturn(false);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame('FAIL', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }
}
