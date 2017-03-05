<?php

namespace PayumTW\Collect;

class Encrypter
{
    /**
     * $key.
     *
     * @var string
     */
    public $key;

    /**
     * setKey.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * encrypt.
     *
     * @param array $params
     * @param array $filterKeys
     * @return string
     */
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

    /**
     * filter.
     * @param array $params
     * @param array $filterKeys
     * @return array
     */
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

    /**
     * hash.
     *
     * @param array $params
     * @param string $separate
     * @return string
     */
    protected function hash($params, $separate = '$')
    {
        return hash('md5', implode($separate, $params));
    }
}
