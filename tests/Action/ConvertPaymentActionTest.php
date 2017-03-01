<?php

namespace PayumTW\Collect\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;
use PayumTW\Collect\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new ConvertPaymentAction();
        $request = new Convert(
            $payment = m::mock('Payum\Core\Model\PaymentInterface'),
            $to = 'array'
        );
        $payment->shouldReceive('getDetails')->once()->andReturn([]);
        $payment->shouldReceive('getNumber')->once()->andReturn($number = 'foo');
        $payment->shouldReceive('getClientEmail')->once()->andReturn($clientEmail = 'foo');
        $payment->shouldReceive('getTotalAmount')->once()->andReturn($totalAmount = 'foo');
        $payment->shouldReceive('getDescription')->once()->andReturn($description = 'foo');

        $action->execute($request);
        $this->assertSame([
            'cust_order_no' => $number,
            'order_amount' => $totalAmount,
            'order_detail' => $description,
            'cust_order_number' => $number,
            'payer_email' => $clientEmail,
        ], $request->getResult());
    }
}
