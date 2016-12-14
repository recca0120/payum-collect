<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Collect\CollectCvsGatewayFactory;

class CollectCvsGatewayFactoryTest extends PHPUnit_Framework_TestCase
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

        $custId = 'foo.cust_id';
        $custPassword = 'foo.cust_password';
        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $gateway = new CollectCvsGatewayFactory();
        $config = $gateway->createConfig([
            'api' => false,
            'cust_id' => $custId,
            'cust_password' => $custPassword,
            'payum.http_client' => $httpClient,
            'httplug.message_factory' => $messageFactory,
        ]);
        $api = call_user_func($config['payum.api'], ArrayObject::ensureArrayObject($config));

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($custId, $config['cust_id']);
        $this->assertSame($custPassword, $config['cust_password']);
        $this->assertInstanceOf('PayumTW\Collect\CollectCvsApi', $api);
    }
}
