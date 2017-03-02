<?php

namespace PayumTW\Collect\Tests;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use PayumTW\Collect\CollectCvsApi;

class CollectCvsApiTest extends TestCase
{
    const TIMEZONE = 'Asia/Taipei';

    protected function tearDown()
    {
        m::close();
    }

    public function testCreateTransaction()
    {
        $api = new CollectCvsApi(
            $options = [
                'cust_id' => 'foo',
                'cust_password' => 'foo',
                'submit_type' => 'redirect',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'cust_order_number' => 'foo',
            'order_amount' => 'foo',
            'expire_date' => Carbon::now(static::TIMEZONE)->endOfDay()->addDays(7)->toIso8601String(),
            'payer_name' => 'foo',
            'payer_postcode' => 'foo',
            'payer_address' => 'foo',
            'payer_mobile' => 'foo',
        ];

        $this->assertSame(array_merge([
            'cmd' => 'cvs_order_regiater',
            'cust_id' => $options['cust_id'],
            'cust_password' => $options['cust_password'],
        ], $params), $api->createTransaction($params));
    }

    public function testCreateTransactionXml()
    {
        $api = new CollectCvsApi(
            $options = [
                'cust_id' => 'foo',
                'cust_password' => 'foo',
                'submit_type' => 'xml',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'cust_order_number' => 'foo',
            'order_amount' => 'foo',
            'expire_date' => Carbon::now(static::TIMEZONE)->endOfDay()->addDays(7)->toIso8601String(),
            'payer_name' => 'foo',
            'payer_postcode' => 'foo',
            'payer_address' => 'foo',
            'payer_mobile' => 'foo',
        ];

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(''),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            m::on(function($xml) use ($options, $params) {
                return implode('', array_map('trim', preg_split('/\n|\r\n/', '<?xml version="1.0" encoding="UTF-8"?>
                    <request>
                        <header>
                            <cmd>cvs_order_regiater</cmd>
                            <cust_id>'.$options['cust_id'].'</cust_id>
                            <cust_password>'.$options['cust_password'].'</cust_password>
                        </header>
                        <order>
                            <cust_order_number>'.$params['cust_order_number'].'</cust_order_number>
                            <order_amount>'.$params['order_amount'].'</order_amount>
                            <expire_date>'.$params['expire_date'].'</expire_date>
                            <payer_name>'.$params['payer_name'].'</payer_name>
                            <payer_postcode>'.$params['payer_postcode'].'</payer_postcode>
                            <payer_address>'.$params['payer_address'].'</payer_address>
                            <payer_mobile>'.$params['payer_mobile'].'</payer_mobile>
                        </order>
                    </request>'))) === $xml;
            })
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $contents ='<?xml version="1.0" encoding="UTF-8"?>
                <response>
                    <status>OK</status>
                    <order>
                        <cust_order_number>12362</cust_order_number>
                        <order_amount>50</order_amount>
                        <expire_date>2011-07-30</expire_date>
                        <st_barcode1>000730619</st_barcode1>
                        <st_barcode2>9821100000059300</st_barcode2>
                        <st_barcode3>000764000000050</st_barcode3>
                        <post_barcode1></post_barcode1>
                        <post_barcode2></post_barcode2>
                        <post_barcode3></post_barcode3>
                        <virtual_account>98211000000593</virtual_account>
                        <ibon_code>121100000594</ibon_code>
                        <bill_amount>50</bill_amount>
                        <cs_fee>0</cs_fee>
                        <ibon_shopid>CCAT</ibon_shopid>
                    </order>
                </response>'
        );

        $this->assertSame([
            'status' => 'OK',
            'orders' => [
                [
                    'cust_order_number' => '12362',
                    'order_amount' => '50',
                    'expire_date' => '2011-07-30',
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
                ],
            ],
        ], $api->createTransaction($params));
    }

    public function testGetTransactionData()
    {
        $api = new CollectCvsApi(
            $options = [
                'cust_id' => 'foo',
                'cust_password' => 'foo',
                'submit_type' => 'xml',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'process_code_update_time_begin' => Carbon::now(static::TIMEZONE)->toIso8601String(),
            'process_code_update_time_end' => Carbon::now(static::TIMEZONE)->addDays(1)->toIso8601String(),
        ];

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            $api->getApiEndpoint(''),
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            m::on(function($xml) use ($options, $params) {
                return implode('', array_map('trim', preg_split('/\n|\r\n/', '<?xml version="1.0" encoding="UTF-8"?>
                    <request>
                        <header>
                            <cmd>cvs_order_query</cmd>
                            <cust_id>'.$options['cust_id'].'</cust_id>
                            <cust_password>'.$options['cust_password'].'</cust_password>
                        </header>
                        <query>
                            <process_code_update_time_begin>'.$params['process_code_update_time_begin'].'</process_code_update_time_begin>
                            <process_code_update_time_end>'.$params['process_code_update_time_end'].'</process_code_update_time_end>
                        </query>
                    </request>'))) === $xml;
            })
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getStatusCode')->once()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $contents ='<?xml version="1.0" encoding="UTF-8"?>
                <response>
                    <status>OK</status>
                    <order>
                        <cust_order_number>12346</cust_order_number>
                        <order_amount>5000</order_amount>
                        <expire_date>2011-09-20 00:00:00</expire_date>
                        <st_barcode1>000920619</st_barcode1>
                        <st_barcode2>9821100000048000</st_barcode2>
                        <st_barcode3>000944000005000</st_barcode3>
                        <post_barcode1></post_barcode1>
                        <post_barcode2></post_barcode2>
                        <post_barcode3></post_barcode3>
                        <virtual_account>98211000000480</virtual_account>
                        <ibon_code>126300000488</ibon_code>
                        <bill_amount>5000</bill_amount>
                        <ibon_shopid>CCAT</ibon_shopid>
                        <create_time>2011-05-10 02:57:26</create_time>
                        <process_code>1</process_code>
                        <pay_date></pay_date>
                        <grant_amount></grant_amount>
                        <grant_date></grant_date>
                    </order>
                    <order>
                        <cust_order_number>12360</cust_order_number>
                        <order_amount>50</order_amount>
                        <expire_date>2011-07-30 00:00:00</expire_date>
                        <st_barcode1>000730619</st_barcode1>
                        <st_barcode2>9821100000057100</st_barcode2>
                        <st_barcode3>000742000000050</st_barcode3>
                        <post_barcode1></post_barcode1>
                        <post_barcode2></post_barcode2>
                        <post_barcode3></post_barcode3>
                        <virtual_account>98211000000571</virtual_account>
                        <ibon_code>121100000579</ibon_code>
                        <bill_amount>50</bill_amount>
                        <ibon_shopid>CCAT</ibon_shopid>
                        <create_time>2011-05-29 03:05:29</create_time>
                        <process_code>3</process_code>
                        <pay_date></pay_date>
                        <grant_amount></grant_amount>
                        <grant_date></grant_date>
                    </order>
                </response>'
        );

        $this->assertSame([
            'status' => 'OK',
            'orders' => [
                [
                    'cust_order_number' => '12346',
                    'order_amount' => '5000',
                    'expire_date' => '2011-09-20 00:00:00',
                    'st_barcode1' => '000920619',
                    'st_barcode2' => '9821100000048000',
                    'st_barcode3' => '000944000005000',
                    'post_barcode1' => '',
                    'post_barcode2' => '',
                    'post_barcode3' => '',
                    'virtual_account' => '98211000000480',
                    'ibon_code' => '126300000488',
                    'bill_amount' => '5000',
                    'ibon_shopid' => 'CCAT',
                    'create_time' => '2011-05-10 02:57:26',
                    'process_code' => '1',
                    'pay_date' => '',
                    'grant_amount' => '',
                    'grant_date' => '',
                ],
                [
                    'cust_order_number' => '12360',
                    'order_amount' => '50',
                    'expire_date' => '2011-07-30 00:00:00',
                    'st_barcode1' => '000730619',
                    'st_barcode2' => '9821100000057100',
                    'st_barcode3' => '000742000000050',
                    'post_barcode1' => '',
                    'post_barcode2' => '',
                    'post_barcode3' => '',
                    'virtual_account' => '98211000000571',
                    'ibon_code' => '121100000579',
                    'bill_amount' => '50',
                    'ibon_shopid' => 'CCAT',
                    'create_time' => '2011-05-29 03:05:29',
                    'process_code' => '3',
                    'pay_date' => '',
                    'grant_amount' => '',
                    'grant_date' => '',
                ],
            ],
        ], $api->getTransactionData($params));
    }
}
