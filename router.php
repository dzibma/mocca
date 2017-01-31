<?php

/**
 * Mocca - a PHP micro-router for RESTful web services and APIs
 * 
 * @author Martin Dzíbela <martin@dzibela.cz>
 * @license MIT
 */

namespace Mocca;

/**
 * Add route
 *
 * @param null|string|array $methods HTTP method(s)
 * @param null|string $mask URL regex
 * @param callable $action
 * @return array
 */
function route($methods, $mask, $action) {

    static $routes;
    if ($routes === null) {
        $routes = [];
    }

    if ($action !== null) {
        $routes[] = [$methods, $mask, $action];
    }

    return $routes;
}

/**
 * Start routing
 * 
 * @param null|string $method;
 * @param null|string $uri
 */
function run($method = null, $uri = null) {

    if ($uri === null) {
        $uri = strstr($_SERVER['REQUEST_URI'], '?', true) ?: $_SERVER['REQUEST_URI'];
    }

    $httpMethod = strtoupper($method !== null ? $method : $_SERVER['REQUEST_METHOD']);
    if ($httpMethod === 'POST' && isset($_SERVER['X_HTTP_METHOD_OVERRIDE'])) {
        $httpMethod = strtoupper($_SERVER['X_HTTP_METHOD_OVERRIDE']);
    }

    $routes = route(null, null, null);
    $current = 0;
    $count = count($routes);
    while ($current < $count) {
        list($methods, $mask, $action) = $routes[$current++];

        $supports = is_array($methods)
            ? in_array($httpMethod, array_map('strtoupper', $methods))
            : $methods === null ?: strtoupper($methods) === $httpMethod;

        if ($supports) {
            if ($mask && strpos($mask, ':') !== false) {
                $mask = strtr($mask, [
                    '/:all?' => '(?:/(.+))?',
                    ':all' => '(.+)',
                    '/:any?' => '(?:/([^/]+))?',
                    ':any' => '([^/]+)',
                    '/:int?' => '(?:/(\d+))?',
                    ':int' => '(\d+)'
                ]);
            }

            if ($mask === null || preg_match("#^$mask()$#", $uri, $matches)) {
                $args = [];
                $i = 1;
                while (isset($matches[$i + 1])) {
                    $value = $matches[$i++];
                    $args[] = $value === '' ? null : $value;
                }

                call_user_func_array($action, $args);

                $routes = route(null, null, null);
                $current = $count;
                $count = count($routes);
            }
        }
    }

}
