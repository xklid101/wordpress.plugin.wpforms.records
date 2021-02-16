<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;

class Config
{
    const CONFIG_PARAM_NAME = 'xklid101_wprecords';

    const ERRORMSG_FORM_MAXCOUNT_DEFAULT = 'Formulář již nelze odeslat! Počet záznamů dosáhl maxima!';
    const ERRORMSG_FIELD_MAXCOUNT_DEFAULT = 'Formulář již nelze odeslat! Počet unikátních záznamů pro toto pole dosáhl maxima!';
    const ERRORMSG_FIELD_REQGROUP_DEFAULT = 'Alespoň jedno pole musí být vyplněno!';

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

