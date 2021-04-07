<?php

declare(strict_types=1);
/**
 * This file is part of Simps.
 *
 * @link     https://simps.io
 * @document https://doc.simps.io
 * @license  https://github.com/simple-swoole/simps/blob/master/LICENSE
 */
namespace Simps\Utils;

class Env
{
    protected $env;

    /**
     * 加载配置文件.
     */
    public function __construct()
    {
        $str = @file_get_contents(BASE_PATH . '/.env');
        $arr = explode("\n", $str);
        foreach ($arr as $v) {
            $v = $this->parse($v);
            if ($v) {
                $this->env[$v[0]] = $v[1];
            }
        }
    }

    /**
     * 获取环境变量.
     * @param $key
     * @param null $default
     * @return null|mixed
     */
    public function get($key, $default = null)
    {
        return $this->env[$key] ?? $default;
    }

    /**
     * 解析加载项.
     * @param $str
     * @return null|array
     */
    protected function parse($str)
    {
        $r = strpos($str, '=');
        if (! $r) {
            return null;
        }
        $key = trim(substr($str, 0, $r));
        if (! $key) {
            return null;
        }
        $j = strpos($str, '#');
        if ($j === false) {
            $val = trim(substr($str, $r + 1));
        } else {
            $val = trim(substr($str, $r + 1, $j - $r - 1));
        }
        switch ($val) {
            case 'true':
            case '(true)':
                return [$key, true];
            case 'false':
            case '(false)':
                return [$key, false];
            case 'empty':
            case '(empty)':
                return [$key, ''];
            case 'null':
            case '(null)':
                return [$key, null];
            default:
                return [$key, $val];
        }
    }
}
