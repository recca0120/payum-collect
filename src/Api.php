<?php

namespace PayumTW\Collect;

use Carbon\Carbon;
use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\Http\HttpException;

class Api
{
    /**
     * TIMEZONE.
     *
     * @var string
     */
    const TIMEZONE = 'Asia/Taipei';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, array $fields, $type = 'cancel')
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint($type), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return string
     */
    public function getApiEndpoint($type = 'capture')
    {
        $urls = [
            'capture' => 'https://4128888card.com.tw/cocs/client_order_append.php',
            'cancel' => 'https://4128888card.com.tw/cocs/client_order_cancel.php',
            'refund' => 'https://4128888card.com.tw/cocs/client_order_refund.php',
        ];

        return $urls[$type];
        // return $this->options['sandbox'] ? 'https://4128888card.com.tw/cocs/client_order_append.php' : 'https://4128888card.com.tw/cocs/client_order_append.php';
    }

    /**
     * createTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function createTransaction(array $params)
    {
        $supportedParams = [
            'link_id' => $this->options['link_id'],
            'cust_order_no' => null,
            'order_amount' => null,
            'order_detail' => null,
            /*
             * 指定分期參數
             * esun.normal 玉山銀行一次性付款
             * esun.m3 玉山銀行 3 期
             * esun.m6 玉山銀行 6 期
             * esun.m9 玉山銀行 9 期
             */
            'limit_product_id' => null,
            'send_time' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            /*
             * 回傳方式
             * redirect（直接重新導向）、
             * plain（純文字）、
             * xml（XML 格 式）、
             * json（JSON 格式）。
             */
            'return_type' => 'redirect',
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['chk'] = $this->calculateHash([
            $params['order_amount'],
            $params['send_time'],
        ]);

        return $params;
    }

    /**
     * cancelTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function cancelTransaction(array $params)
    {
        $supportedParams = [
            'link_id' => $this->options['link_id'],
            'cust_order_no' => null,
            'order_amount' => null,
            'send_time' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            /*
             * 回傳方式
             * redirect（直接重新導向）、
             * plain（純文字）、
             * xml（XML 格 式）、
             * json（JSON 格式）。
             */
            'return_type' => 'json',
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['chk'] = $this->calculateHash([
            $params['cust_order_no'],
            $params['order_amount'],
            $params['send_time'],
        ]);

        return $this->doRequest('GET', $params, 'cancel');
    }

    /**
     * refundTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function refundTransaction(array $params)
    {
        $supportedParams = [
            'link_id' => $this->options['link_id'],
            'cust_order_no' => null,
            'order_amount' => null,
            'refund_amount' => null,
            'send_time' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            /*
             * 回傳方式
             * redirect（直接重新導向）、
             * plain（純文字）、
             * xml（XML 格 式）、
             * json（JSON 格式）。
             */
            'return_type' => 'json',
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['chk'] = $this->calculateHash([
            $params['cust_order_no'],
            $params['order_amount'],
            $params['refund_amount'],
            $params['send_time'],
        ]);

        return $this->doRequest('GET', $params, 'refund');
    }

    /**
     * getTransactionData.
     *
     * @param mixed $params
     *
     * @return array
     */
    public function getTransactionData(array $params)
    {
        $details = [];

        if (isset($params['response']) === true) {
            if ($this->verifyHash($params['response']) === false) {
                $params['status'] = '-1';

                return $params;
            }

            $details = array_merge($details, $params['response']);
        }

        return $details;
    }

    /**
     * Verify if the hash of the given parameter is correct.
     *
     * @param array $params
     *
     * @return bool
     */
    public function verifyHash(array $params)
    {
        $hashKey = 'chk';

        if (isset($params['status']) === true) {
            $hashKey = 'checksum';
            $data = $data = $this->only($params, [
                'api_id',
                'trans_id',
                'amount',
                'status',
                'nonce',
            ]);
        } else if ($params['ret'] === 'OK') {
            $data = $this->only($params, [
                'order_amount',
                'send_time',
                'ret',
                'acquire_time',
                'auth_code',
                'card_no',
                'notify_time',
                'cust_order_no',
            ]);

        } else if ($params['ret'] === 'FAIL'){
            $data = $data = $this->only($params, [
                'order_amount',
                'send_time',
                'ret',
                'notify_time',
                'cust_order_no',
            ]);
        }

        return $params[$hashKey] === $this->calculateHash($data);
    }

    protected function only($array, $keys) {
        $results = [];
        foreach ($keys as $key) {
            if (isset($array[$key]) === true) {
                $results[$key] = $array[$key];
            }
        }

        return $results;
    }

    /**
     * calculateHash.
     *
     * @param array $params
     *
     * @return string
     */
    protected function calculateHash($params)
    {
        if (isset($params['status']) === true) {
            $symbol = ':';
        } else {
            $symbol = '$';
            $params = array_merge([
                $this->options['hash_base'],
            ], $params);
        }

        return md5(implode($symbol, $params));
    }
}
