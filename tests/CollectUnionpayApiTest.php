<?php

use Mockery as m;
use Carbon\Carbon;
use PayumTW\Collect\CollectUnionpayApi;

class CollectUnionpayApiTest extends PHPUnit_Framework_TestCase
{
    const TIMEZONE = 'Asia/Taipei';

    public function tearDown()
    {
        m::close();
    }

    public function test_create_transaction()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $orderAmount = 12345;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];
        $order = [
            'cust_order_no' => '12345',
            'order_amount' => $orderAmount,
            'order_detail' => '訂單範例 abc - 1234',
            'limit_product_id' => 'esun.m12|esun.m3',
            'send_time' => $sendTime,
        ];
        $chk = md5($hashBase.'$'.$orderAmount.'$'.$sendTime);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);
        $params = $api->createTransaction($order);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($chk, $params['chk']);
    }

    public function test_refund_transaction()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');
        $request = m::spy('Psr\Http\Message\RequestInterface');
        $response = m::spy('Psr\Http\Message\ResponseInterface');
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $custOrderNo = 12345;
        $orderAmount = 12345;
        $refundAmount = 123;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];
        $order = [
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'refund_amount' => $refundAmount,
            'send_time' => $sendTime,
        ];
        $chk = md5($hashBase.'$'.$custOrderNo.'$'.$orderAmount.'$'.$refundAmount.'$'.$sendTime);

        $query = http_build_query([
            'link_id' => $linkId,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'refund_amount' => $refundAmount,
            'send_time' => $sendTime,
            'return_type' => 'json',
            'chk' => $chk,
        ]);

        $result = [
            'status' => 'OK',
            'cust_order_no' => '20120403001282',
            'refund_amount' => '12000',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        $messageFactory
            ->shouldReceive('createRequest')->with('GET', $api->getApiEndpoint('refund'), $headers, $query)->andReturn($request);

        $httpClient
            ->shouldReceive('send')->with($request)->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody->getContents')->andReturn(json_encode($result));

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($result, $api->refundTransaction($order));
        $messageFactory->shouldHaveReceived('createRequest')->with('GET', $api->getApiEndpoint('refund'), $headers, $query)->once();
        $httpClient->shouldHaveReceived('send')->with($request)->once();
        $response->shouldHaveReceived('getStatusCode')->twice();
        $response->shouldHaveReceived('getBody')->once();
    }

    public function test_vertify_hash_when_ret_is_ok()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $ret = 'OK';
        $custOrderNo = 12345;
        $orderAmount = 12345;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $acquireTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $authCode = '156348';
        $cardNo = '6200';
        $notifyTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $chk = md5($hashBase.'$'.$orderAmount.'$'.$sendTime.'$'.$ret.'$'.$acquireTime.'$'.$authCode.'$'.$cardNo.'$'.$notifyTime.'$'.$custOrderNo);

        $returnValue = [
            'ret' => $ret,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
            'acquire_time' => $acquireTime,
            'auth_code' => $authCode,
            'card_no' => $cardNo,
            'notify_time' => $notifyTime,
            'chk' => $chk,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertTrue($api->verifyHash($returnValue));
    }

    public function test_vertify_hash_when_ret_is_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $ret = 'FAIL';
        $custOrderNo = 12345;
        $orderAmount = 12345;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $notifyTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $chk = md5($hashBase.'$'.$orderAmount.'$'.$sendTime.'$'.$ret.'$'.$notifyTime.'$'.$custOrderNo);

        $returnValue = [
            'ret' => $ret,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
            'notify_time' => $notifyTime,
            'chk' => $chk,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertTrue($api->verifyHash($returnValue));
    }

    public function test_get_transaction_data_form_request()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $ret = 'OK';
        $custOrderNo = 12345;
        $orderAmount = 12345;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $acquireTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $authCode = '156348';
        $cardNo = '6200';
        $notifyTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $chk = md5($hashBase.'$'.$orderAmount.'$'.$sendTime.'$'.$ret.'$'.$acquireTime.'$'.$authCode.'$'.$cardNo.'$'.$notifyTime.'$'.$custOrderNo);

        $returnValue = [
            'ret' => $ret,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
            'acquire_time' => $acquireTime,
            'auth_code' => $authCode,
            'card_no' => $cardNo,
            'notify_time' => $notifyTime,
            'chk' => $chk,
        ];

        $details = [
            'response' => $returnValue,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($returnValue, $api->getTransactionData($details));
    }

    public function test_get_transaction_data_form_request_when_verify_hash_is_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $ret = 'OK';
        $custOrderNo = 12345;
        $orderAmount = 12345;
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $acquireTime = Carbon::now(static::TIMEZONE)->toDateTimeString();
        $authCode = '156348';
        $cardNo = '6200';
        $notifyTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $chk = 'a'.md5($hashBase.'$'.$orderAmount.'$'.$sendTime.'$'.$ret.'$'.$acquireTime.'$'.$authCode.'$'.$cardNo.'$'.$notifyTime.'$'.$custOrderNo);

        $returnValue = [
            'ret' => $ret,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
            'acquire_time' => $acquireTime,
            'auth_code' => $authCode,
            'card_no' => $cardNo,
            'notify_time' => $notifyTime,
            'chk' => $chk,
        ];

        $details = [
            'response' => $returnValue,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame([
            'status' => '-1',
        ], $api->getTransactionData($details));
    }

    public function test_get_transaction_data_form_apn()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $apiId = 'CC0000000001';
        $transId = '550e8400e29b41d4a716446655440000';
        $orderNo = 'PO5488277';
        $amount = 1250;
        $status = 'B';
        $paymentCode = 1;
        $paymentDetail = [
            'auth_code' => '123456',
            'auth_card_no' => '0000',
        ];
        $memo = [];
        $expireTime = '2013-09-28T08:15:00+08:00';
        $createTime = '2013-09-28T08:00:00+08:00';
        $modifyTime = '2013-09-28T08:30:00+08:00';
        $nonce = '1234569999';

        $checksum = md5($apiId.':'.$transId.':'.$amount.':'.$status.':'.$nonce);

        $returnValue = [
            'api_id' => $apiId,
            'trans_id' => $transId,
            'order_no' => $orderNo,
            'amount' => $amount,
            'status' => $status,
            'payment_code' => $paymentCode,
            'payment_detail' => $paymentDetail,
            'memo' => $memo,
            'expire_time' => $expireTime,
            'create_time' => $createTime,
            'modify_time' => $modifyTime,
            'nonce' => $nonce,
            'checksum' => $checksum,
        ];

        $details = $returnValue;

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($returnValue, $api->getTransactionData($details));
    }

    public function test_get_transaction_data_form_apn_when_verify_hash_is_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];

        $apiId = 'CC0000000001';
        $transId = '550e8400e29b41d4a716446655440000';
        $orderNo = 'PO5488277';
        $amount = 1250;
        $status = 'B';
        $paymentCode = 1;
        $paymentDetail = [
            'auth_code' => '123456',
            'auth_card_no' => '0000',
        ];
        $memo = [];
        $expireTime = '2013-09-28T08:15:00+08:00';
        $createTime = '2013-09-28T08:00:00+08:00';
        $modifyTime = '2013-09-28T08:30:00+08:00';
        $nonce = '1234569999';

        $checksum = 'a'.md5($apiId.':'.$transId.':'.$amount.':'.$status.':'.$nonce);

        $returnValue = [
            'api_id' => $apiId,
            'trans_id' => $transId,
            'order_no' => $orderNo,
            'amount' => $amount,
            'status' => $status,
            'payment_code' => $paymentCode,
            'payment_detail' => $paymentDetail,
            'memo' => $memo,
            'expire_time' => $expireTime,
            'create_time' => $createTime,
            'modify_time' => $modifyTime,
            'nonce' => $nonce,
            'checksum' => $checksum,
        ];

        $details = $returnValue;

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectUnionpayApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame([
            'status' => '-1',
        ], $api->getTransactionData($details));
    }
}
