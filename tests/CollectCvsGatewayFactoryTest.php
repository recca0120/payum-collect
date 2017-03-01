<?php

namespace PayumTW\Collect\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\CollectCvsGatewayFactory;

class CollectCvsGatewayFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateConfig()
    {
        $gateway = new CollectCvsGatewayFactory();
        $config = $gateway->createConfig([
            'cust_id' => 'foo',
            'cust_password' => 'foo',
            'submit_type' => 'redirect',
            'payum.api' => false,
            'payum.required_options' => [],
            'payum.http_client' => $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            'httplug.message_factory' => $messageFactory = m::mock('Http\Message\MessageFactory'),
        ]);

        $this->assertInstanceOf(
            'PayumTW\Collect\Api',
            $config['payum.api'](ArrayObject::ensureArrayObject($config))
        );
    }
}
