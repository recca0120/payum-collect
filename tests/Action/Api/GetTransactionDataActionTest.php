<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\GetTransactionData, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');
        $input = [
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'refund_amount' => 'foo.refund_amount',
        ];
        $details = new ArrayObject($input);

        $endpoint = 'foo.endpoint';
        $data = ['foo.data'];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('getTransactionData')->andReturn($details);

        $action = new GetTransactionDataAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('getTransactionData')->with($input)->once();
    }
}
