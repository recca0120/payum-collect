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
     * @param array $options
     * @param HttpClientInterface $client
     * @param MessageFactory $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory, Encrypter $encrypter = null)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->encrypter = $encrypter ?: new Encrypter();
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, $params, $type = 'cancel', $isJson = true)
    {
        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint($type), [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], is_array($params) === true ? http_build_query($params) : $params);

        $response = $this->client->send($request);

        $statusCode = $response->getStatusCode();
        if (false == ($statusCode >= 200 && $statusCode < 300)) {
            throw HttpException::factory($request, $response);
        }

        $contents = $response->getBody()->getContents();

        return $isJson === true
            ? json_decode($contents, true)
            : $contents;
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
        $filters = [
            'ok' => ['order_amount', 'send_time', 'ret', 'acquire_time', 'auth_code', 'card_no', 'notify_time', 'cust_order_no'],
            'fail' => ['order_amount', 'send_time', 'ret', 'notify_time', 'cust_order_no'],
            'status' => ['api_id', 'trans_id', 'amount', 'status', 'nonce'],
        ];

        if (isset($params['status']) === true) {
            $hashKey = 'checksum';
            $filterKeys = $filters['status'];
        } else {
            $hashKey = 'chk';
            $filterKeys = $filters[strtolower($params['ret'])];
        }

        return $params[$hashKey] === $this->calculateHash($params, $filterKeys);
    }

    /**
     * calculateHash.
     *
     * @param array $params
     *
     * @return string
     */
    protected function calculateHash($params, $filterKeys = [])
    {
        return $this->encrypter
            ->setKey($this->options['hash_base'])
            ->encrypt($params, $filterKeys);
    }
}
