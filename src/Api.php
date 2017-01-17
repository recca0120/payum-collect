<?php

namespace PayumTW\Collect;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\Http\HttpException;

abstract class Api
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
    protected function doRequest($method, $body, $type = 'cancel', $isJson = true)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        if (is_array($body) === true) {
            $body = http_build_query($body);
        }

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint($type), $headers, $body);

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $contents = $response->getBody()->getContents();

        return $isJson === true ? json_decode($contents, true) : $contents;
    }

    /**
     * @return string
     */
    abstract public function getApiEndpoint($type = 'capture');

    /**
     * createTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    abstract public function createTransaction(array $params);

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
            $data = $this->only($params, [
                'api_id',
                'trans_id',
                'amount',
                'status',
                'nonce',
            ]);
        } elseif ($params['ret'] === 'OK') {
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
        } elseif ($params['ret'] === 'FAIL') {
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

    protected function only($array, $keys)
    {
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
