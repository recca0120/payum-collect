<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\Api\CancelTransactionAction;

class CancelTransactionActionTest extends PHPUnit_Framework_TestCase
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

        $request = m::spy('PayumTW\Collect\Request\Api\CancelTransaction, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');

        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
        ]);

        $endpoint = 'foo.endpoint';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('cancelTransaction')->with($details->toUnsafeArray())->andReturn($details->toUnsafeArray());

        $action = new CancelTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('cancelTransaction')->with($details->toUnsafeArray())->once();
    }

    /**
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function test_throw_exception_when_api_is_error()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\CancelTransaction, ArrayAccess');
        $api = m::spy('stdClass');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $action = new CancelTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */
    }
}
