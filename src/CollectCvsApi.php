<?php

namespace PayumTW\Collect;

use Carbon\Carbon;

class CollectCvsApi extends Api
{
    /**
     * @return string
     */
    public function getApiEndpoint($type = 'capture')
    {
        return 'https://www.ccat.com.tw/cvs/ap_interface.php';
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
            'cmd' => 'cvs_order_regiater',
            'cust_id' => $this->options['cust_id'],
            'cust_password' => $this->options['cust_password'],
            'cust_order_number' => null,
            'order_amount' => null,
            'expire_date' => Carbon::now(static::TIMEZONE)->addDays(7)->toDateTimeString(),
            'payer_name' => null,
            'payer_postcode' => null,
            'payer_address' => null,
            'payer_mobile' => null,
            'payer_email' => null,
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        return $params;
    }
}
