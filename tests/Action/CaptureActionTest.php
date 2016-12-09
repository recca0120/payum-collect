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
        $token = m::spy('Payum\Core\Model\TokenInterface');
        $genericTokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $notifyToken = m::spy('Payum\Core\Model\TokenInterface');

        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'order_detail' => 'foo.order_detail',
        ]);

        $targetUrl = 'http://localhost/payment/capture/FEDHD1o-fvtpZqM6QvtNsy_qoLX_8x4QXvfyE94mIZc';

        $gatewayName = 'foo.gateway';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)
            ->shouldReceive('getToken')->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn($targetUrl)
            ->shouldReceive('getGatewayName')->andReturn($gatewayName)
            ->shouldReceive('getDetails')->andReturn($details);

        $genericTokenFactory
            ->shouldReceive('createNotifyToken')->with($gatewayName, $details)->andReturn($notifyToken);

        $action = new CaptureAction();
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($genericTokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $request->shouldHaveReceived('getToken')->once();
        $token->shouldHaveReceived('getTargetUrl')->once();
        $token->shouldHaveReceived('setTargetUrl')->with('http://localhost/payment/capture')->once();
        $token->shouldHaveReceived('save')->once();
        $token->shouldHaveReceived('getGatewayName')->once();
        $token->shouldHaveReceived('getDetails')->once();
        $genericTokenFactory->shouldHaveReceived('createNotifyToken')->with($gatewayName, $details)->once();
        $notifyToken->shouldHaveReceived('setHash')->with(md5($details['cust_order_no']))->once();
        $notifyToken->shouldHaveReceived('save')->once();
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
        $genericTokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');

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
        $action->setGenericTokenFactory($genericTokenFactory);
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
