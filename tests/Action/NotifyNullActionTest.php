<?php

namespace PayumTW\Collect\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Collect\Action\NotifyNullAction;

class NotifyNullActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new NotifyNullAction();
        $request = new Notify(null);

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = ['order_no' => 'foo'];
        $gateway->shouldReceive('execute')->once()->with(m::on(function ($getHttpRequest) use ($response) {
            $getHttpRequest->request = $response;

            return $getHttpRequest instanceof GetHttpRequest;
        }));

        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetToken'))
            ->andReturnUsing(function ($getToken) use ($response) {
                $this->assertSame($getToken->getHash(), md5($response['order_no']));
            });
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Notify'));

        $action->execute($request);
    }
}
