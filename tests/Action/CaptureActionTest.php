<?php

namespace PayumTW\Collect\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Collect\Action\CaptureAction;

class CaptureActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CaptureAction;
        $request = m::mock(new Capture(new ArrayObject(['cust_order_no' => 'foo'])));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($getHttpRequest) {
            return $getHttpRequest instanceof GetHttpRequest;
        }));

        $request->shouldReceive('getToken')->once()->andReturn(
            $token = m::mock('Payum\Core\Model\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'http://dev/payum/collect/');
        $token->shouldReceive('setTargetUrl')->once()->with($targetUrl = 'http://dev/payum');
        $token->shouldReceive('save')->once();

        $action->setGenericTokenFactory(
            $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface')
        );

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName = 'foo');
        $token->shouldReceive('getDetails')->once()->andReturn($details = ['foo' => 'bar']);

        $tokenFactory->shouldReceive('createNotifyToken')->once()->with($gatewayName, $details)->andReturn(
            $notifyToken = m::mock('Payum\Core\Model\TokenInterface')
        );

        $notifyToken->shouldReceive('setHash')->once()->with(md5($request->getModel()['cust_order_no']));
        $notifyToken->shouldReceive('save')->once();

        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Collect\Request\Api\CreateTransaction'));

        $action->execute($request);
    }

    public function testCapture()
    {
        $action = new CaptureAction;
        $request = m::mock(new Capture(new ArrayObject(['cust_order_no' => 'foo'])));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = ['ret' => 'OK'];
        $gateway->shouldReceive('execute')->once()->with(m::on(function ($getHttpRequest) use ($response) {
            $getHttpRequest->request = $response;

            return $getHttpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('verifyHash')->once()->with($response)->andReturn(false);

        $action->execute($request);

        $this->assertSame([
            'cust_order_no' => 'foo',
            'ret' => 'FAIL',
        ], (array) $request->getModel());
    }

    public function testCaptureCVS()
    {
        $action = new CaptureAction;
        $request = m::mock(new Capture(new ArrayObject(['cust_order_no' => 'foo'])));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = ['status' => 'OK'];
        $gateway->shouldReceive('execute')->once()->with(m::on(function ($getHttpRequest) use ($response) {
            $getHttpRequest->request = $response;

            return $getHttpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('verifyHash')->once()->with($response)->andReturn(false);

        $action->execute($request);

        $this->assertSame([
            'cust_order_no' => 'foo',
            'status' => 'ERROR',
        ], (array) $request->getModel());
    }
}
