<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\CollectUnionpayGatewayFactory;

class CollectUnionpayGatewayFactoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_config()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $linkId = 'foo.link_id';
        $hashBase = 'foo.hash_base';
        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');
        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $gateway = new CollectUnionpayGatewayFactory();
        $config = $gateway->createConfig([
            'api' => false,
            'link_id' => $linkId,
            'hash_base' => $hashBase,
            'payum.http_client' => $httpClient,
            'httplug.message_factory' => $messageFactory,
        ]);
        $api = call_user_func($config['payum.api'], ArrayObject::ensureArrayObject($config));

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($linkId, $config['link_id']);
        $this->assertSame($hashBase, $config['hash_base']);
        $this->assertInstanceOf('PayumTW\Collect\CollectUnionpayApi', $api);
    }
}
