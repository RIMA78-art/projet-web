<?php
session_start();

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$legacyMap = [
    'login' => 'user/login',
    'register' => 'user/register',
    'logout' => 'user/logout',
    'dashboard' => 'dashboard/index',
    'programmes' => 'programme/index',
    'coachs' => 'coach/index',
];

$route = $_GET['route'] ?? null;
if ($route === null && isset($_GET['action']) && isset($legacyMap[$_GET['action']])) {
    $route = $legacyMap[$_GET['action']];
}
$route ??= 'dashboard/index';

[$controllerName, $method] = array_pad(explode('/', $route), 2, 'index');
$controllerClass = ucfirst($controllerName) . 'Controller';

if (!class_exists($controllerClass)) {
    http_response_code(404);
    exit('Controller introuvable');
}

$controller = new $controllerClass();
if (!method_exists($controller, $method)) {
    http_response_code(404);
    exit('Action introuvable');
}

$controller->$method();
