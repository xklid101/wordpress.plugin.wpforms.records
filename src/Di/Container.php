<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Di;

use RuntimeException;
use ReflectionClass;

class Container
{
    private array $stack = [];

    public function get(string $id, ...$args)
    {
        $idx = $id . md5(serialize($args));
        if (isset($this->stack[$idx])) {
            return $this->stack[$idx];
        }

        if (!$this->has($id)) {
            throw new RuntimeException("Class '$id' not found!");
        }

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
        return class_exists($id);
    }
}

