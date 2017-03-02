<?php

namespace PayumTW\Collect;

use Carbon\Carbon;

class XmlGenerator
{
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
