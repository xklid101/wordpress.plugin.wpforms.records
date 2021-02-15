<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

class Config
{
    const CONFIG_PARAM_NAME = 'xklid101_wprecords';

    private $config;

    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function get($param = '', $default = null)
    {
        if ($param) {
            return $this->config[$param] ?? $default;
        }
        return $this->config;
    }

    public function getWp(): array
    {
        return get_option(self::CONFIG_PARAM_NAME) ?: [];
    }

    public function setWp(array $allValues)
    {
        if (!get_option(self::CONFIG_PARAM_NAME)) {
            add_option(self::CONFIG_PARAM_NAME, []);
        }
        update_option(self::CONFIG_PARAM_NAME, $allValues);
    }
}

