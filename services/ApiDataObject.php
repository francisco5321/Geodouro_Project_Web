<?php

namespace app\services;

use yii\base\BaseObject;

class ApiDataObject extends BaseObject
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    protected static function first(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return $default;
    }
}
