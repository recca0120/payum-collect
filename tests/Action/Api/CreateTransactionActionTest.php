<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException   \Payum\Core\Reply\HttpPostRedirect
     */
    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\CreateTransaction, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');
        $details = new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'order_detail' => 'foo.order_detail',
        ]);

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
            ->shouldReceive('getApiEndpoint')->andReturn($endpoint)
            ->shouldReceive('createTransaction')->andReturn($data);

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('getApiEndpoint')->once();
        $api->shouldHaveReceived('createTransaction')->with((array) $details)->once();
    }

    public function test_create_transaction_by_xml()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\CreateTransaction, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');
        $details = m::spy(new ArrayObject([
            'cust_order_no' => 'foo.cust_order_no',
            'order_amount' => 'foo.order_amount',
            'order_detail' => 'foo.order_detail',
        ]));

        $endpoint = 'foo.endpoint';
        $data = ['status' => 'OK'];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('createTransaction')->andReturn($data);

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('createTransaction')->with((array) $details)->once();
        $details->shouldHaveReceived('replace')->with($data)->once();
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

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */
    }
}
