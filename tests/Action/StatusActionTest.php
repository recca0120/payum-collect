<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\StatusAction;

class StatusActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_mark_new()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markNew')->once();
    }

    public function test_mark_captured_when_ret_is_ok()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'ret' => 'OK',
            'cust_order_no' => '20120403000003',
            'order_amount' => '12345',
            'send_time' => '2013-04-03 07:17:25',
            'acquire_time' => '2013-04-03 07:19:32',
            'auth_code' => '851425',
            'card_no' => '0085',
            'notify_time' => '2013-04-03 07:19:46',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markCaptured')->once();
    }

    public function test_mark_failed_when_ret_is_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'ret' => 'FAIL',
            'cust_order_no' => '20120403000003',
            'order_amount' => '12345',
            'send_time' => '2013-04-03 07:17:25',
            'notify_time' => '2013-04-03 07:19:46',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markFailed')->once();
    }

    public function test_mark_canceled_when_status_is_ok()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'status' => 'OK',
            'cust_order_no' => '20120403001276',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markCanceled')->once();
    }

    public function test_mark_refunded_when_status_is_ok()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'status' => 'OK',
            'cust_order_no' => '20120403001276',
            'refund_amount' => 12000,
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markRefunded')->once();
    }

    public function test_mark_failed_when_status_is_error()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'status' => 'ERROR',
            'send_time' => '異常',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived('markFailed')->once();
    }

    public function test_apn_status_is_b()
    {
        $this->validateApnStatus('B', 'markCaptured');
    }

    public function test_apn_status_is_o()
    {
        $this->validateApnStatus('O', 'markSuspended');
    }

    public function test_apn_status_is_e()
    {
        $this->validateApnStatus('E', 'markCaptured');
    }

    public function test_apn_status_is_f()
    {
        $this->validateApnStatus('F', 'markFailed');
    }

    public function test_apn_status_is_d()
    {
        $this->validateApnStatus('D', 'markExpired');
    }

    public function test_apn_status_is_p()
    {
        $this->validateApnStatus('P', 'markCaptured');
    }

    public function test_apn_status_is_m()
    {
        $this->validateApnStatus('M', 'markCanceled');
    }

    public function test_apn_status_is_n()
    {
        $this->validateApnStatus('N', 'markFailed');
    }

    public function test_apn_status_is_q()
    {
        $this->validateApnStatus('Q', 'markRefunded');
    }

    public function test_apn_status_is_r()
    {
        $this->validateApnStatus('R', 'markFailed');
    }

    public function validateApnStatus($status, $marked)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => $status,
            'payment_code' => 1,
            'payment_detail' => [
                'auth_code' => '123456',
                'auth_card_no' => '0000',
            ],
            'memo' => [],
            'expire_time' => '2013-09-28T08:15:00+08:00',
            'create_time' => '2013-09-28T08:00:00+08:00',
            'modify_time' => '2013-09-28T08:30:00+08:00',
            'nonce' => '1234569999',
            'checksum' => '1d1e6c42757166243312b2ad05a5dda8',
        ]);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived($marked)->once();
    }
}
