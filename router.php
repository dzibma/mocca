<?php

namespace Mocca;

/**
 * Mocca - a PHP micro-router for RESTful web services and APIs.
 * 
 * @copyright 2016 Martin Dzíbela <martin@dzibela.cz>
 * @license   https://github.com/dzibma/mocca/blob/master/LICENSE MIT
 */

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
 */
function run() {

    $uri = strstr($_SERVER['REQUEST_URI'], '?', true) ?: $_SERVER['REQUEST_URI'];
    $httpMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    if ($httpMethod === 'POST' && isset($_SERVER['X_HTTP_METHOD_OVERRIDE'])) {
        $httpMethod = strtoupper($_SERVER['X_HTTP_METHOD_OVERRIDE']);
    }

    $i = 0;
    while ($i < $count = count($routes = route(null, null, null))) {
        list($methods, $mask, $action) = $routes[$i++];

        $supports = is_array($methods)
            ? in_array($httpMethod, array_map('strtoupper', $methods))
            : $methods === null ?: strtoupper($methods) === $httpMethod;

        if ($supports) {
            if ($mask && strpos($mask, ':') !== false) {
                $mask = strtr($mask, [
                    '/:any?' => '(?:/(.+))?',
                    ':any' => '(.+)',
                    '/:string?' => '(?:/([^/]+))?',
                    ':string' => '([^/]+)',
                    '/:float?' => '(?:/([.\d]+))?',
                    ':float' => '([.\d]+)',
                    '/:int?' => '(?:/(\d+))?',
                    ':int' => '(\d+)'
                ]);
            }

            if ($mask === null || preg_match("#^$mask()$#", $uri, $m)) {
                call_user_func_array($action, isset($m) ? array_slice($m, 1) : []);
                $i = $count;
            }
        }
    }

}
