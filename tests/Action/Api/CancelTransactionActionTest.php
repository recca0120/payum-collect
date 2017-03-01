<?php

namespace PayumTW\Collect\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Request\Api\CancelTransaction;
use PayumTW\Collect\Action\Api\CancelTransactionAction;

class CancelTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CancelTransactionAction();
        $request = new CancelTransaction(new ArrayObject($details = [
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
        ]));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('cancelTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        $action->execute($request);

        $this->assertSame(array_merge($details, $params), (array) $request->getModel());
    }
}
