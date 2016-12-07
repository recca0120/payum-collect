<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\CaptureAction;

class CaptureActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_redirect_to_gateway()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Capture');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'order_detail' => 'foo.order_detail',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $action = new CaptureAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Collect\Request\Api\CreateTransaction'))->once();
    }

    public function test_captured()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Capture');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'order_detail' => 'foo.order_detail',
        ]);

        $data = [
            'ret' => 'OK',
            'cust_order_no' => '20120403000003',
            'order_amount' => '12345',
            'send_time' => '2013-04-03 07:17:25',
            'acquire_time' => '2013-04-03 07:19:32',
            'auth_code' => '851425',
            'card_no' => '0085',
            'notify_time' => '2013-04-03 07:19:46',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($httpRquest) use ($data) {
                $httpRquest->request = $data;

                return $httpRquest;
            });

        $action = new CaptureAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\Sync'))->once();
    }
}
