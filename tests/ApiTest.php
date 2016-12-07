<?php

use Mockery as m;
use Carbon\Carbon;
use PayumTW\Collect\Api;

class ApiTest extends PHPUnit_Framework_TestCase
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

        $api = new Api($options, $httpClient, $messageFactory);
        $params = $api->createTransaction($order);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($chk, $params['chk']);
    }

    public function test_cancel_transaction()
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
        $sendTime = Carbon::now(static::TIMEZONE)->toDateTimeString();

        $options = [
            'link_id' => $linkId,
            'hash_base' => $hashBase,
        ];
        $order = [
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
        ];
        $chk = md5($hashBase.'$'.$custOrderNo.'$'.$orderAmount.'$'.$sendTime);

        $query = http_build_query([
            'link_id' => $linkId,
            'cust_order_no' => $custOrderNo,
            'order_amount' => $orderAmount,
            'send_time' => $sendTime,
            'return_type' => 'json',
            'chk' => $chk,
        ]);

        $result = [
            'status' => 'OK',
            'cust_order_no' => '20120403001282',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $messageFactory);

        $messageFactory
            ->shouldReceive('createRequest')->with('GET', $api->getApiEndpoint('cancel'), $headers, $query)->andReturn($request);

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

        $this->assertSame($result, $api->cancelTransaction($order));
        $messageFactory->shouldHaveReceived('createRequest')->with('GET', $api->getApiEndpoint('cancel'), $headers, $query)->once();
        $httpClient->shouldHaveReceived('send')->with($request)->once();
        $response->shouldHaveReceived('getStatusCode')->twice();
        $response->shouldHaveReceived('getBody')->once();
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

        $api = new Api($options, $httpClient, $messageFactory);

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

        $api = new Api($options, $httpClient, $messageFactory);

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

        $api = new Api($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertTrue($api->verifyHash($returnValue));
    }
}
