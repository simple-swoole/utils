<?php

declare(strict_types=1);
/**
 * This file is part of Simps.
 *
 * @link     https://simps.io
 * @document https://doc.simps.io
 * @license  https://github.com/simple-swoole/simps/blob/master/LICENSE
 */
if (! function_exists('getInstance')) {
    function getInstance($class)
    {
        return ($class)::getInstance();
    }
}
if (! function_exists('config')) {
    function config($name, $default = null)
    {
        return getInstance(\Simps\Utils\Config::class)->get($name, $default);
    }
}
if (! function_exists('container')) {
    function container($key = null, $value = null)
    {
        if ($key == null) {
            return \Simps\Utils\Container::instance();
        }
        if ($value == null) {
            return \Simps\Utils\Container::instance()->singleton($key);
        }
        return \Simps\Utils\Container::instance()->set($key, $value);
    }
}
if (! function_exists('collection')) {
    function collection($data = [])
    {
        return new \Simps\Utils\Collection($data);
    }
}
if (! function_exists('env')) {
    function env($key, $default = null)
    {
        return container()->get(\Simps\Utils\Env::class)->get($key, $default);
    }
}

if (! function_exists('printEol')) {
    /**
     * printEol.
     * @param $expression
     */
    function printEol($expression)
    {
        print_r($expression);
        echo PHP_EOL;
    }
}

if (! function_exists('echoSuccess')) {
    /**
     * printEol.
     * @param $msg
     */
    function echoSuccess($msg)
    {
        printEol('[' . date('Y-m-d H:i:s') . '] [INFO] ' . "\033[32m{$msg}\033[0m");
    }
}

if (! function_exists('echoError')) {
    /**
     * printEol.
     * @param $msg
     */
    function echoError($msg)
    {
        printEol('[' . date('Y-m-d H:i:s') . '] [ERROR] ' . "\033[31m{$msg}\033[0m");
    }
}
