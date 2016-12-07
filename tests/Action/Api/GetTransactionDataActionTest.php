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

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\GetTransactionData, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');

        $details = m::mock(new ArrayObject());

        $endpoint = 'foo.endpoint';

        $data = ['status' => '1'];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('getTransactionData')->andReturn($data);

        $action = new GetTransactionDataAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('getTransactionData')->once();
        $details->shouldHaveReceived('replace')->once();
    }

    public function test_get_transaction_data_when_status_is_error()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('PayumTW\Collect\Request\Api\GetTransactionData, ArrayAccess');
        $api = m::spy('PayumTW\Collect\Api');

        $details = m::mock(new ArrayObject());

        $endpoint = 'foo.endpoint';

        $data = ['status' => '-1'];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('getTransactionData')->andReturn($data);

        $action = new GetTransactionDataAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->execute($request);
        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('getTransactionData')->once();
        $details->shouldNotHaveReceived('replace');
    }

    /**
     * @expectedException Payum\Core\Exception\UnsupportedApiException
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

        $action = new GetTransactionDataAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */
    }
}
