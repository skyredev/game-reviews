<?php
/**
 * @file router.php
 * @brief Simple routing mechanism
 */

require_once __DIR__ . '/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = parse_url(APP_BASE, PHP_URL_PATH);
$path = trim(str_replace($base, '', $uri), '/');
global $pdo;

$routes = [
    '' => 'HomeController@showHomePage',
    'home' => 'HomeController@showHomePage',
    'top' => 'TopController@showTopPage',
    'login' => 'AuthController@showLoginPage',
    'register' => 'AuthController@showRegisterPage',
    'admin' => 'AdminController@showAdminPage',
];

if (isset($routes[$path])) {
    [$controllerName, $method] = explode('@', $routes[$path]);
    $controllerPath = __DIR__ . '/../controllers/' . $controllerName . '.php';

    if (!file_exists($controllerPath)) {
        http_response_code(500);
        exit("Controller $controllerName not found.");
    }

    require_once $controllerPath;

    if (!function_exists($method)) {
        http_response_code(500);
        exit("Method $method not found in $controllerName.");
    }

    $method($pdo);
} else {
    http_response_code(404);
    require __DIR__ . '/../views/404.php';
}
