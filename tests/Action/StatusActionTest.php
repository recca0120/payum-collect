<?php

namespace PayumTW\Collect\Tests\Action;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\Action\StatusAction;

class StatusActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testMarkNew()
    {
        $this->validate([], 'markNew');
    }

    public function testMarkCaptured()
    {
        $this->validate([
            'link_id' => '0xwRd4gYVBHo',
            'cust_order_no' => '',
            'order_amount' => '12345',
            'order_detail' => '訂單範例 abc - 1234',
            'limit_product_id' => 'esun.m12|esun.m3',
            'send_time' => '2012-04-03 07:17:25',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
            'return_type' => 'redirect',

            'status' => 'OK',
            'cust_order_no' => '20120403001273',
        ], 'markCaptured');

        $this->validate([
            'ret' => 'OK',
            'cust_order_no' => '20120403000003',
            'order_amount' => '12345',
            'send_time' => '2013-04-03 07:17:25',
            'acquire_time' => '2013-04-03 07:19:32',
            'auth_code' => '851425',
            'card_no' => '0085',
            'notify_time' => '2013-04-03 07:19:46',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
        ], 'markCaptured');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'B',
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
        ], 'markCaptured');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'E',
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
        ], 'markCaptured');
    }

    public function testMarkFailed()
    {
        $this->validate([
            'link_id' => '0xwRd4gYVBHo',
            'cust_order_no' => '',
            'order_amount' => '12345',
            'order_detail' => '訂單範例 abc - 1234',
            'limit_product_id' => 'esun.m12|esun.m3',
            'send_time' => '2012-04-03 07:17:25',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
            'return_type' => 'redirect',

            'status' => 'ERROR',
            'msg' => 'send_time 異常',
        ], 'markFailed');

        $this->validate([
            'ret' => 'FAIL',
            'cust_order_no' => '20120403000003',
            'order_amount' => '12345',
            'send_time' => '2013-04-03 07:17:25',
            'notify_time' => '2013-04-03 07:19:46',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
        ], 'markFailed');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'F',
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
        ], 'markFailed');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'P',
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
        ], 'markFailed');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'N',
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
        ], 'markFailed');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'R',
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
        ], 'markFailed');

        $this->validate([
            'cmd' => 'cvs_order_regiater',
            'cust_id' => 'CV0100000001',
            'cust_password' => 'CV0100000001',
            'cust_order_number' => '12362',
            'order_amount' => '50',
            'expire_date' => '2011-07-30T00:00:00+08:00',
            'payer_name' => '測試人 2',
            'payer_postcode' => '',
            'payer_address' => '測試地址 2',
            'payer_mobile' => '0927119471',
            'payer_email' => 'have@niceday.co',

            'st_barcode1' => '000730619',
            'st_barcode2' => '9821100000059300',
            'st_barcode3' => '000764000000050',
            'post_barcode1' => '',
            'post_barcode2' => '',
            'post_barcode3' => '',
            'virtual_account' => '98211000000593',
            'ibon_code' => '121100000594',
            'bill_amount' => '50',
            'cs_fee' => '0',
            'ibon_shopid' => 'CCAT',

            'status' => 'ERROR',
        ], 'markFailed');
    }

    public function testMarkCanceled()
    {
        $this->validate([
            'link_id' => '0xwRd4gYVBHo',
            'cust_order_no' => 'Order00001',
            'order_amount' => '12345',
            'send_time' => '2012-04-03 07:17:25',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
            'return_type' => 'xml',

            'status' => 'OK',
            'cust_order_no' => '20120403001273',
        ], 'markCanceled');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'M',
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
        ], 'markCanceled');
    }

    public function testMarkRefunded()
    {
        $this->validate([
            'link_id' => '0xwRd4gYVBHo',
            'cust_order_no' => '',
            'order_amount' => '12345',
            'refund_amount' => '12000',
            'send_time' => '2012-04-03 07:17:25',
            'chk' => 'a1eeb49d7a559393d05f5bbd81fbba84',
            'return_type' => 'plain',

            'status' => 'OK',
            'cust_order_no' => '20120403001273',
            'refund_amount' => '12000',
        ], 'markRefunded');

        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'Q',
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
        ], 'markRefunded');
    }

    public function testMarkSuspended()
    {
        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'O',
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
        ], 'markSuspended');
    }

    public function testMarkExpired()
    {
        $this->validate([
            'api_id' => 'CC0000000001',
            'trans_id' => '550e8400e29b41d4a716446655440000',
            'order_no' => 'PO5488277',
            'amount' => 1250,
            'status' => 'D',
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
        ], 'markExpired');
    }

    public function testMarkPending()
    {
        $this->validate([
            'cmd' => 'cvs_order_regiater',
            'cust_id' => 'CV0100000001',
            'cust_password' => 'CV0100000001',
            'cust_order_number' => '12362',
            'order_amount' => '50',
            'expire_date' => '2011-07-30T00:00:00+08:00',
            'payer_name' => '測試人 2',
            'payer_postcode' => '',
            'payer_address' => '測試地址 2',
            'payer_mobile' => '0927119471',
            'payer_email' => 'have@niceday.co',

            'st_barcode1' => '000730619',
            'st_barcode2' => '9821100000059300',
            'st_barcode3' => '000764000000050',
            'post_barcode1' => '',
            'post_barcode2' => '',
            'post_barcode3' => '',
            'virtual_account' => '98211000000593',
            'ibon_code' => '121100000594',
            'bill_amount' => '50',
            'cs_fee' => '0',
            'ibon_shopid' => 'CCAT',

            'status' => 'OK',
        ], 'markPending');
    }

    protected function validate($input, $type)
    {
        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $request->shouldReceive('getModel')->andReturn($details = new ArrayObject($input));
        $request->shouldReceive($type)->once();

        $action->execute($request);
    }
}
