<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends PHPUnit_Framework_TestCase
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

        $request = m::spy('Payum\Core\Request\Convert');
        $source = m::spy('Payum\Core\Model\PaymentInterface');
        $details = new ArrayObject();

        $custOrderNo = uniqid();
        $orderAmount = 1000;
        $orderDetail = 'foo.description';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getSource')->andReturn($source)
            ->shouldReceive('getTo')->andReturn('array');

        $source
            ->shouldReceive('getDetails')->andReturn($details)
            ->shouldReceive('getNumber')->andReturn($custOrderNo)
            ->shouldReceive('getTotalAmount')->andReturn($orderAmount)
            ->shouldReceive('getDescription')->andReturn($orderDetail);

        $action = new ConvertPaymentAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getSource')->twice();
        $request->shouldHaveReceived('getTo')->once();
        $source->shouldHaveReceived('getDetails')->once();
        $source->shouldHaveReceived('getNumber')->once();
        $source->shouldHaveReceived('getTotalAmount')->once();
        $source->shouldHaveReceived('getDescription')->once();
        $request->shouldHaveReceived('setResult')->with([
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'order_detail' => $orderDetail,
        ])->once();
    }
}
