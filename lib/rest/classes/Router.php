<?php

declare(strict_types=1);

namespace Supclub\Rest\Classes;

/**
 *
 */
class Router
{
    /**
     * @var array
     */
    private $routes;

    /**
     * @param $pattern
     * @param $function
     * @return void
     */
    public function addRoute($pattern, $function): void
    {
        $this->routes['{' . $pattern . '}'] = $function;
    }

    /**
     * @return false|mixed
     */
    public function run()
    {
        foreach ($this->routes as $pattern => $function) {
            try {
                if (preg_match($pattern, $_SERVER['REQUEST_URI'], $params)) {
                    array_shift($params);
                    array_unshift($params, $_SERVER['REQUEST_URI']);
                    return call_user_func_array($function, array_values($params));
                }
            } catch (\Exception $exception) {
                echo '<pre>' . print_r($exception, true) . '</pre>';
            }
        }

        return false;
    }
}