<?php

namespace PayumTW\Collect;

use Carbon\Carbon;

class CollectApi extends CollectUnionpayApi
{
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

        $params['chk'] = $this->calculateHash($params, [
            'cust_order_no', 'order_amount', 'send_time'
        ]);

        return $this->doRequest('GET', $params, 'cancel');
    }
}
