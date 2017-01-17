<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\CancelAction;

class CancelActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_cancel()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Cancel');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $action = new CancelAction();
        $action->setGateway($gateway);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Collect\Request\Api\CancelTransaction'))->once();
    }
}
