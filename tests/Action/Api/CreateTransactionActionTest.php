<?php

namespace PayumTW\Collect\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;
use PayumTW\Collect\Request\Api\CreateTransaction;
use PayumTW\Collect\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction($details = new ArrayObject([
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
            'order_detail' => 'foo',
        ]));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('createTransaction')->once()->with((array) $details)->andReturn($params = ['foo' => 'bar']);
        $api->shouldReceive('getApiEndpoint')->once()->andReturn($apiEndpoint = 'foo');

        try {
            $action->execute($request);
        } catch (HttpPostRedirect $e) {
            $this->assertSame($apiEndpoint, $e->getUrl());
            $this->assertSame($params, $e->getFields());
        }
    }

    public function testExecuteByXML()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction($details = new ArrayObject([
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
            'order_detail' => 'foo',
        ]));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('createTransaction')->once()->with((array) $details)->andReturn($params = ['status' => 'OK']);

        $action->execute($request);
        $this->assertSame($params['status'], $details['status']);
    }
}
