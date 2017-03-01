<?php

namespace PayumTW\Collect\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Request\Api\RefundTransaction;
use PayumTW\Collect\Action\Api\RefundTransactionAction;

class RefundTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new RefundTransactionAction();
        $request = new RefundTransaction(new ArrayObject($details = [
            'cust_order_no' => 'foo',
            'order_amount' => 100,
            'refund_amount' => 100,
        ]));

        $action->setApi(
            $api = m::mock('PayumTW\Collect\Api')
        );

        $api->shouldReceive('refundTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['status' => 'OK']);

        $action->execute($request);

        $this->assertSame(array_merge($details, $params), (array) $request->getModel());
    }
}
