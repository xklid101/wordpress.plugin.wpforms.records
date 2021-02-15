<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Di;

use RuntimeException;
use ReflectionClass;
use Xklid101\Wprecords\Services\Routing;
use Xklid101\Wprecords\Services\Template;
use Xklid101\Wprecords\Services\Config;
use WPForms\WPForms;

class Container
{
    private array $config;

    private array $stack = [];

    /**
     * array to register methods to create sesrvices
     */
    private array $registered = [
        Routing::class => 'getRouting',
        Template::class => 'getTemplate',
        WPForms::class => 'getWpforms',
        Config::class => 'getConfig'
    ];

    /**
     * Class constructor
     *
     * @param array $config container configuration params
     */
    public function __construct(array $config) {
        $this->config = $config;
    }

    private function getWpforms()
    {
        return WPForms::instance();
    }

    private function getConfig()
    {
        return new Config(
            $this->config
        );
    }

    private function getTemplate()
    {
        return new Template(
            $this->get(Routing::class),
            $this->get(Config::class)->get('baseSrcDir')
        );
    }

    private function getRouting()
    {
        return new Routing(
            $this
        );
    }

    public function get(string $id, ...$args)
    {
        $idx = $id . md5(serialize($args));
        if (isset($this->stack[$idx])) {
            return $this->stack[$idx];
        }

        if (!$this->has($id)) {
            throw new RuntimeException("Class '$id' not found!");
        }

        // registered methods to get requested class
        if (isset($this->registered[$id])) {
            $this->stack[$idx] = $this->{$this->registered[$id]}();
            return $this->stack[$idx];
        }

        // limited auto creating of instances
        $className = $id;
        $constructor = (new ReflectionClass($className))->getConstructor();
        $params = [];
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                if (!$param->allowsNull() && $param->getClass()) {
                    $paramClassName = $param->getClass()->getName();
                    if ($this->has($paramClassName)) {
                        $params[$paramClassName] = $this->get($paramClassName, ...$args);
                    }
                }
            }
            foreach ($args as $arg) {
                if (is_object($arg)) {
                    /**
                     * overwrite same instance parameters by using class name
                     */
                    $params[get_class($arg)] = $arg;
                    continue;
                }
                $params[] = $arg;
            }
        }
        $service = new $className(...array_values($params));

        $this->stack[$idx] = $service;
        return $this->stack[$idx];
    }

    public function has(string $id)
    {
        if (isset($this->registered[$id])) {
            return true;
        }
        return class_exists($id);
    }
}

