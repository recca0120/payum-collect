<?php

namespace PayumTW\Collect\Tests;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PayumTW\Collect\CollectUnionpayApi;

class CollectUnionpayApiTest extends TestCase
{
    const TIMEZONE = 'Asia/Taipei';

    protected function tearDown()
    {
        m::close();
    }

    public function testCreateTransaction()
    {
        $api = new CollectUnionpayApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'cust_order_no' => '12345',
            'order_amount' => 12345,
            'order_detail' => '訂單範例 abc - 1234',
            'limit_product_id' => 'esun.m12|esun.m3',
            'send_time' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            'return_type' => 'redirect',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with(array_merge([
            'link_id' => $options['link_id'],
        ], $params), ['order_amount', 'send_time'])->andReturn($encrypt = 'foo');

        $this->assertSame(array_merge(
            ['link_id' => $options['link_id']],
            $params,
            ['chk' => $encrypt]
        ), $api->createTransaction($params));
    }

    public function testRefundTransaction()
    {
        $api = new CollectUnionpayApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'cust_order_no' => 12345,
            'order_amount' => 12345,
            'refund_amount' => 123,
            'send_time' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            'return_type' => 'json',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with(array_merge([
            'link_id' => $options['link_id'],
        ], $params), ['cust_order_no', 'order_amount', 'refund_amount', 'send_time'])->andReturn($encrypt = 'foo');

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'GET',
            $api->getApiEndpoint('refund'),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query(array_merge([
                'link_id' => $options['link_id'],
            ], $params, [
                'chk' => $encrypt
            ]))
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $contents = json_encode(['foo' => 'bar'])
        );

        $this->assertSame(json_decode($contents, true), $api->refundTransaction($params));
    }

    public function testVerifyHashRetOK()
    {
        $api = new CollectUnionpayApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'ret' => 'OK',
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
            'send_time' => 'foo',
            'acquire_time' => 'foo',
            'auth_code' => 'foo',
            'card_no' => 'foo',
            'notify_time' => 'foo',
            'chk' => 'foo',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with($params, [
            'order_amount', 'send_time', 'ret', 'acquire_time', 'auth_code', 'card_no', 'notify_time', 'cust_order_no'
        ])->andReturn('foo');

        $this->assertSame(true, $api->verifyHash($params));
    }

    public function testVerifyHashRetFail()
    {
        $api = new CollectUnionpayApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'ret' => 'FAIL',
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
            'send_time' => 'foo',
            'notify_time' => 'foo',
            'chk' => 'foo',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with($params, [
            'order_amount', 'send_time', 'ret', 'notify_time', 'cust_order_no'
        ])->andReturn('foo');

        $this->assertSame(true, $api->verifyHash($params));
    }

    public function testVerifyHashApn()
    {
        $api = new CollectUnionpayApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'api_id' => 'foo',
            'trans_id' => 'foo',
            'order_no' => 'foo',
            'amount' => 'foo',
            'status' => 'foo',
            'payment_code' => 'foo',
            'payment_detail' => 'foo',
            'memo' => 'foo',
            'expire_time' => 'foo',
            'create_time' => 'foo',
            'modify_time' => 'foo',
            'nonce' => 'foo',
            'checksum' => 'foo',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with($params, [
            'api_id', 'trans_id', 'amount', 'status', 'nonce'
        ])->andReturn('foo');

        $this->assertSame(true, $api->verifyHash($params));
    }
}
