<?php

namespace PayumTW\Collect;

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
}
