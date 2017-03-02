<?php

namespace PayumTW\Collect;

class Encrypter
{
    public $key = null;

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function encrypt($params, $filterKeys = [])
    {
        if (empty($filterKeys) === false) {
            $params = $this->filter($params, $filterKeys);
        }

        return isset($params['status']) === true
            ? $this->hash($params, ':')
            : $this->hash(array_merge([$this->key], $params), '$');


        if (isset($params['status']) === true) {
            return hash('md5', implode(':', $params));
        }

        return hash('md5', implode($this->separate, $params));
    }

    protected function filter($params, $filterKeys)
    {
        $results = [];
        foreach ($filterKeys as $key) {
            if (isset($params[$key]) === true) {
                $results[$key] = $params[$key];
            }
        }

        return $results;
    }

    protected function hash($params, $separate = '$')
    {
        return hash('md5', implode($separate, $params));
    }
}
