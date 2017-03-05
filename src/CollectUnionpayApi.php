<?php

namespace PayumTW\Collect;

use Carbon\Carbon;

class CollectUnionpayApi extends Api
{
    /**
     * @return string
     */
    public function getApiEndpoint($type = 'capture')
    {
        $urls = [
            'capture' => 'https://4128888card.com.tw/cocs/client_unionpay_append.php ',
            'refund' => 'https://4128888card.com.tw/cocs/client_unionpay_refund.php ',
        ];

        return $urls[$type];
        // return $this->options['sandbox'] ? 'https://4128888card.com.tw/cocs/client_order_append.php' : 'https://4128888card.com.tw/cocs/client_order_append.php';
    }

    /**
     * createTransaction.
     *
     * @param array $params
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

        $params['chk'] = $this->calculateHash($params, ['order_amount', 'send_time']);

        return $params;
    }

    /**
     * refundTransaction.
     *
     * @param array $params
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

        $params['chk'] = $this->calculateHash($params, [
            'cust_order_no', 'order_amount', 'refund_amount', 'send_time',
        ]);

        return $this->doRequest('GET', $params, 'refund');
    }
}
