<?php

namespace PayumTW\Collect\Tests;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PayumTW\Collect\CollectApi;

class CollectApiTest extends TestCase
{
    const TIMEZONE = 'Asia/Taipei';

    protected function tearDown()
    {
        m::close();
    }

    public function testCancelTransaction()
    {
        $api = new CollectApi(
            $options = [
                'link_id' => 'foo',
                'hash_base' => 'foo',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter = m::mock('PayumTW\Collect\Encrypter')
        );

        $params = [
            'cust_order_no' => 'foo',
            'order_amount' => 'foo',
            'send_time' => 'foo',
            'return_type' => 'json',
        ];

        $encrypter->shouldReceive('setKey')->once()->with($options['hash_base'])->andReturnSelf();
        $encrypter->shouldReceive('encrypt')->once()->with(array_merge([
            'link_id' => $options['link_id'],
        ], $params), ['cust_order_no', 'order_amount', 'send_time'])->andReturn($encrypt = 'foo');

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'GET',
            $api->getApiEndpoint('cancel'),
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

        $this->assertSame(json_decode($contents, true), $api->cancelTransaction($params));
    }
}
