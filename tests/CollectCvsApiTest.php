<?php

use Mockery as m;
use Carbon\Carbon;
use PayumTW\Collect\CollectCvsApi;

class CollectCvsApiTest extends PHPUnit_Framework_TestCase
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

        $custId = 'foo.cust_id';
        $custPassword = 'foo.cust_password';
        $custOrderNumber = 'foo.cust_order_number';
        $orderAmount = 12345;
        $expireDate = Carbon::now(static::TIMEZONE)->endOfDay()->addDays(7)->toIso8601String();
        $payerName = 'payer_name';
        $payerPostcode = 'payer_postcode';
        $payerAddress = 'payer_address';
        $payerMobile = 'payer_mobile';
        $payerEmail = 'payer_email';

        $options = [
            'cust_id' => $custId,
            'cust_password' => $custPassword,
        ];

        $order = [
            'cust_order_number' => $custOrderNumber,
            'order_amount' => $orderAmount,
            'expire_date' => $expireDate,
            'payer_name' => $payerName,
            'payer_postcode' => $payerPostcode,
            'payer_address' => $payerAddress,
            'payer_mobile' => $payerMobile,
            'payer_email' => $payerEmail,
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = new CollectCvsApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame(array_merge([
            'cmd' => 'cvs_order_regiater',
            'cust_id' => $custId,
            'cust_password' => $custPassword,
        ], $order), $api->createTransaction($order));

        $this->assertSame('https://www.ccat.com.tw/cvs/ap_interface.php', $api->getApiEndpoint());
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

        $api = new CollectCvsApi($options, $httpClient, $messageFactory);

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

        $api = new CollectCvsApi($options, $httpClient, $messageFactory);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame([
            'status' => '-1',
        ], $api->getTransactionData($details));
    }

    public function test_parse_response_xml()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $options = [];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
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
            </response>';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = m::spy(new CollectCvsApi($options, $httpClient, $messageFactory))
            ->shouldAllowMockingProtectedMethods();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

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
        ], $api->parseResponseXML($xml));
    }

    public function test_create_order_register_transaction()
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

        $custId = 'foo.cust_id';
        $custPassword = 'foo.cust_password';
        $custOrderNumber = 'foo.cust_order_number';
        $orderAmount = '12345';
        $expireDate = Carbon::now(static::TIMEZONE)->endOfDay()->addDays(7)->toIso8601String();
        $payerName = 'payer_name';
        $payerPostcode = 'payer_postcode';
        $payerAddress = 'payer_address';
        $payerMobile = 'payer_mobile';
        $payerEmail = 'payer_email';

        $options = [
            'cust_id' => $custId,
            'cust_password' => $custPassword,
        ];

        $order = [
            'cust_order_number' => $custOrderNumber,
            'order_amount' => $orderAmount,
            'expire_date' => $expireDate,
            'payer_name' => $payerName,
            'payer_postcode' => $payerPostcode,
            'payer_address' => $payerAddress,
            'payer_mobile' => $payerMobile,
            'payer_email' => $payerEmail,
        ];

        $responseXML = '<?xml version="1.0" encoding="UTF-8"?>
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
            </response>';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $messageFactory
            ->shouldReceive('createRequest')->andReturn($request);

        $httpClient
            ->shouldReceive('send')->with($request)->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody')->andReturnSelf()
            ->shouldReceive('getContents')->andReturn($responseXML);

        $api = m::spy(new CollectCvsApi($options, $httpClient, $messageFactory))
            ->shouldAllowMockingProtectedMethods();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

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
        ], $api->createTransaction($order, 'xml'));

        $messageFactory->shouldHaveReceived('createRequest')->with('POST', $api->getApiEndpoint('sync'), $headers, m::on(function ($body) use ($api, $options, $order) {
            return $body === $api->createRequestXML($order, 'cvs_order_regiater');
        }))->once();

        $httpClient->shouldHaveReceived('send')->with($request)->once();

        $response->shouldHaveReceived('getStatusCode')->twice();
        $response->shouldHaveReceived('getBody')->once();
        $response->shouldHaveReceived('getContents')->once();
    }

    public function test_parse_response_xml_with_mutiple_orders()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $httpClient = m::spy('Payum\Core\HttpClientInterface');
        $messageFactory = m::spy('Http\Message\MessageFactory');

        $options = [];

        $responseXML = '<?xml version="1.0" encoding="UTF-8"?>
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
            </response>';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $api = m::spy(new CollectCvsApi($options, $httpClient, $messageFactory))
            ->shouldAllowMockingProtectedMethods();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

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
        ], $api->parseResponseXML($responseXML));
    }

    public function test_create_order_query_transaction()
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

        $custId = 'foo.cust_id';
        $custPassword = 'foo.cust_password';
        $custOrderNumber = 'foo.cust_order_number';
        $orderAmount = '12345';
        $expireDate = Carbon::now(static::TIMEZONE)->addDays(7)->toDateTimeString();
        $payerName = 'payer_name';
        $payerPostcode = 'payer_postcode';
        $payerAddress = 'payer_address';
        $payerMobile = 'payer_mobile';
        $payerEmail = 'payer_email';

        $options = [
            'cust_id' => $custId,
            'cust_password' => $custPassword,
        ];

        $order = [
            'process_code_update_time_begin' => Carbon::now(static::TIMEZONE)->toIso8601String(),
            'process_code_update_time_end' => Carbon::now(static::TIMEZONE)->addDays(1)->toIso8601String(),
        ];

        $responseXML = '<?xml version="1.0" encoding="UTF-8"?>
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
            </response>';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $messageFactory
            ->shouldReceive('createRequest')->andReturn($request);

        $httpClient
            ->shouldReceive('send')->with($request)->andReturn($response);

        $response
            ->shouldReceive('getStatusCode')->andReturn(200)
            ->shouldReceive('getBody')->andReturnSelf()
            ->shouldReceive('getContents')->andReturn($responseXML);

        $api = m::spy(new CollectCvsApi($options, $httpClient, $messageFactory))
            ->shouldAllowMockingProtectedMethods();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

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
        ], $api->createOrderQueryTransaction($order));

        $messageFactory->shouldHaveReceived('createRequest')->with('POST', $api->getApiEndpoint('sync'), $headers, m::on(function ($body) use ($api, $options, $order) {
            return $body === $api->createRequestXML($order, 'cvs_order_query');
        }))->once();

        $httpClient->shouldHaveReceived('send')->with($request)->once();

        $response->shouldHaveReceived('getStatusCode')->twice();
        $response->shouldHaveReceived('getBody')->once();
        $response->shouldHaveReceived('getContents')->once();
    }
}
