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
    public function createTransaction(array $params, $returnType = 'xml')
    {
        $cmd = 'cvs_order_regiater';
        $supportedParams = [
            'cust_order_number' => null,
            'order_amount' => null,
            'expire_date' => Carbon::now(static::TIMEZONE)->endOfDay()->addDays(7)->toDateTimeString(),
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

        if (empty($params['expire_date']) === false) {
            $params['expire_date'] = $this->toIso8601String($params['expire_date']);
        }

        return $this->options['submit_type'] === 'redirect' ?
            array_merge([
                'cmd' => $cmd,
                'cust_id' => $this->options['cust_id'],
                'cust_password' => $this->options['cust_password'],
            ], $params) :
            $this->parseResponseXML(
                $this->doRequest('POST', $this->createRequestXML($params, $cmd), 'sync', false)
            );
    }

    /**
     * getTransactionData.
     *
     * @param array $params
     *
     * @return string
     */
    public function getTransactionData(array $params)
    {
        $supportedParams = [
            'process_code_update_time_begin' => Carbon::now(static::TIMEZONE)->toDateTimeString(),
            'process_code_update_time_end' => Carbon::now(static::TIMEZONE)->addDays(1)->toDateTimeString(),
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        $params['process_code_update_time_begin'] = $this->toIso8601String($params['process_code_update_time_begin']);
        $params['process_code_update_time_end'] = $this->toIso8601String($params['process_code_update_time_end']);

        return $this->parseResponseXML(
            $this->doRequest('POST', $this->createRequestXML($params, 'cvs_order_query'), 'sync', false)
        );
    }

    /**
     * toIso8601String.
     *
     * @param string $string
     *
     * @return string
     */
    protected function toIso8601String($string)
    {
        return Carbon::parse($string)->toIso8601String();
    }

    /**
     * createQueryRequestXML.
     *
     * @param array $params
     *
     * @return string
     */
    protected function createRequestXML($params, $cmd = 'cvs_order_regiater')
    {
        $key = $cmd === 'cvs_order_regiater' ? 'order' : 'query';
        $params = [
            'header' => [
                'cmd' => $cmd,
                'cust_id' => $this->options['cust_id'],
                'cust_password' => $this->options['cust_password'],
            ],
            $key => $params,
        ];

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<request>',
        ];

        foreach ($params as $key => $values) {
            $xml[] = '<'.$key.'>';
            foreach ($values as $key2 => $value) {
                $xml[] = '<'.$key2.'>';
                $xml[] = $value;
                $xml[] = '</'.$key2.'>';
            }
            $xml[] = '</'.$key.'>';
        }

        $xml[] = '</request>';

        return implode('', $xml);
    }

    /**
     * parseResponseXML.
     *
     * @param string $xml
     *
     * @return array
     */
    protected function parseResponseXML($xml)
    {
        $result = [
            'status' => 'ERROR',
            'orders' => [],
        ];

        if (preg_match('/<status>(.*)<\/status>/', $xml, $matches) !== false) {
            $result['status'] = $matches[1];
        }

        if (preg_match_all('/<order>(.*)<\/order>/sU', $xml, $matches) !== false) {
            $orders = $matches[1];
            $tags = [
                'cust_order_number',
                'order_amount',
                'expire_date',
                'st_barcode1',
                'st_barcode2',
                'st_barcode3',
                'post_barcode1',
                'post_barcode2',
                'post_barcode3',
                'virtual_account',
                'cs_fee',
                'ibon_code',
                'bill_amount',
                'ibon_shopid',
                'create_time',
                'process_code',
                'pay_date',
                'grant_amount',
                'grant_date',
            ];

            $regexp = '/<(?<key>'.implode('|', $tags).')>(?<value>[^<]*)<\/('.implode('|', $tags).')>/';
            foreach ($orders as $order) {
                $temp = [];
                if (preg_match_all($regexp, $order, $matches, PREG_SET_ORDER) !== false) {
                    foreach ($matches as $match) {
                        $temp[$match['key']] = $match['value'];
                    }
                }
                $result['orders'][] = $temp;
            }
        }

        return $result;
    }
}
