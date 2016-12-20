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
        $this->validate([], 'markNew');
    }

    public function test_mark_captured_when_ret_is_ok()
    {
        $this->validate(['link_id' => 'link_id', 'ret' => 'OK'], 'markCaptured');
    }

    public function test_mark_failed_when_ret_is_fail()
    {
        $this->validate(['link_id' => 'link_id', 'ret' => 'FAIL'], 'markFailed');
    }

    public function test_mark_canceled_when_status_is_ok()
    {
        $this->validate([
            'link_id' => 'link_id',
            'status' => 'OK',
        ], 'markCanceled');
    }

    public function test_mark_refunded_when_status_is_ok()
    {
        $this->validate([
            'link_id' => 'link_id',
            'status' => 'OK',
            'cust_order_no' => '20120403001276',
            'refund_amount' => 12000,
        ], 'markRefunded');
    }

    public function test_mark_failed_when_status_is_error()
    {
        $this->validate([
            'status' => 'ERROR',
            'send_time' => '異常',
        ], 'markFailed');
    }

    public function test_apn_status_is_b()
    {
        $this->validate(['status' => 'B'], 'markCaptured');
    }

    public function test_apn_status_is_o()
    {
        $this->validate(['status' => 'O'], 'markSuspended');
    }

    public function test_apn_status_is_e()
    {
        $this->validate(['status' => 'E'], 'markCaptured');
    }

    public function test_apn_status_is_f()
    {
        $this->validate(['status' => 'F'], 'markFailed');
    }

    public function test_apn_status_is_d()
    {
        $this->validate(['status' => 'D'], 'markExpired');
    }

    public function test_apn_status_is_p()
    {
        $this->validate(['status' => 'P'], 'markCaptured');
    }

    public function test_apn_status_is_m()
    {
        $this->validate(['status' => 'M'], 'markCanceled');
    }

    public function test_apn_status_is_n()
    {
        $this->validate(['status' => 'N'], 'markFailed');
    }

    public function test_apn_status_is_q()
    {
        $this->validate(['status' => 'Q'], 'markRefunded');
    }

    public function test_apn_status_is_r()
    {
        $this->validate(['status' => 'R'], 'markFailed');
    }

    protected function validate($input, $type)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject($input);

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
        $request->shouldHaveReceived($type)->once();
    }
}
