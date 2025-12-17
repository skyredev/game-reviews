<?php
/**
 * @file router.php
 * @brief Simple routing mechanism with middleware support
 * @var PDO $pdo
*/

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/middlewares/MiddlewareStack.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';
require_once __DIR__ . '/middlewares/CsrfMiddleware.php';
require_once __DIR__ . '/middlewares/ValidationMiddleware.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = parse_url(APP_BASE, PHP_URL_PATH);
$path = trim(str_replace($base, '', $uri), '/');

$routes = [
    '' => ['controller' => 'HomeController@showHomePage', 'middleware' => []],
    'home' => ['controller' => 'HomeController@showHomePage', 'middleware' => []],
    'games' => ['controller' => 'GamesController@showGamesPage', 'middleware' => []],
    'game' => ['controller' => 'GamesController@showGamePage', 'middleware' => []],
    'game/review' => [
        'controller' => 'GamesController@submitReview',
        'middleware' => [
            new AuthMiddleware('user'),
            new CsrfMiddleware('/game', 'review'),
            new ValidationMiddleware([
                'rating' => [['required'], ['rating', 1, 10]],
                'comment' => [['required'], ['string'], ['min', 10]]
            ], '/game', 'review')
        ]
    ],
    'game/review/delete' => [
        'controller' => 'GamesController@deleteReview',
        'middleware' => [
            new AuthMiddleware('user'),
            new CsrfMiddleware('/game', 'review')
        ]
    ],
    'api/review/delete' => [
        'controller' => 'GamesController@deleteReview',
        'middleware' => [
            new AuthMiddleware('user'),
            new CsrfMiddleware('/game', 'review')
        ]
    ],
    'api/review/reaction' => [
        'controller' => 'GamesController@toggleReaction',
        'middleware' => [new AuthMiddleware('user')]
    ],
    'games/create' => ['controller' => 'GamesController@showGamesCreatePage', 'middleware' => [new AuthMiddleware('user')]],
    'games/add' => [
        'controller' => 'GamesController@submitGame',
        'middleware' => [
            new AuthMiddleware('user'),
            new CsrfMiddleware('/games/create', 'game'),
            new ValidationMiddleware([
                'title' => [['required'], ['string'], ['min', 1], ['max', 255]],
                'description' => [['required'], ['string'], ['min', 10]],
                'publisher' => [['required'], ['string'], ['min', 1], ['max', 255]],
                'developer' => [['required'], ['string'], ['min', 1], ['max', 255]],
                'release_year' => [['required'], ['year', 1980]],
                'genres' => [['required'], ['array_not_empty']],
                'platforms' => [['required'], ['array_not_empty']],
                'cover_image' => [['required'], ['image'], ['image_max_size', 5242880]] // 5MB
            ], '/games/create', 'game')
        ]
    ],
    'top' => ['controller' => 'TopController@showTopPage', 'middleware' => []],
    'login' => ['controller' => 'AuthController@showLoginPage', 'middleware' => [new AuthMiddleware('guest')]],
    'login/submit' => [
        'controller' => 'AuthController@loginUser',
        'middleware' => [
            new AuthMiddleware('guest'),
            new CsrfMiddleware('/login', 'auth'),
            new ValidationMiddleware([
                'identifier' => [['required']], // Only check if not empty, existence check in model
                'password' => [['required']]
            ], '/login', 'auth')
        ]
    ],
    'register' => ['controller' => 'AuthController@showRegisterPage', 'middleware' => [new AuthMiddleware('guest')]],
    'register/submit' => [
        'controller' => 'AuthController@registerUser',
        'middleware' => [
            new AuthMiddleware('guest'),
            new CsrfMiddleware('/register', 'auth'),
            new ValidationMiddleware([
                'username' => [['required'], ['username'], ['min', 3], ['max', 50]],
                'name' => [['required'], ['string'], ['min', 2], ['max', 100]],
                'email' => [['required'], ['email'], ['email_part_min', 4]],
                'password' => [['required'], ['password']],
                'password_confirmation' => [['required'], ['confirmed']],
                'avatar' => [['image'], ['image_max_size', 2097152]] // 2MB, optional
            ], '/register', 'auth')
        ]
    ],
    'logout' => ['controller' => 'AuthController@logoutUser', 'middleware' => [new AuthMiddleware('user')]],
    'forbidden' => ['controller' => 'UtilController@showForbiddenPage', 'middleware' => []],
    'not-found' => ['controller' => 'UtilController@showNotFoundPage', 'middleware' => []],
    'admin' => ['controller' => 'AdminController@showAdminPage', 'middleware' => [new AuthMiddleware('admin')]],
    'admin/game/approve' => [
        'controller' => 'AdminController@approveGame',
        'middleware' => [
            new AuthMiddleware('admin'),
            new CsrfMiddleware('/admin', 'admin')
        ]
    ],
    'admin/game/reject' => [
        'controller' => 'AdminController@rejectGame',
        'middleware' => [
            new AuthMiddleware('admin'),
            new CsrfMiddleware('/admin', 'admin')
        ]
    ],
    'api/admin/game/approve' => [
        'controller' => 'AdminController@approveGame',
        'middleware' => [
            new AuthMiddleware('admin'),
            new CsrfMiddleware('/admin', 'admin')
        ]
    ],
    'api/admin/game/reject' => [
        'controller' => 'AdminController@rejectGame',
        'middleware' => [
            new AuthMiddleware('admin'),
            new CsrfMiddleware('/admin', 'admin')
        ]
    ],
];

if (isset($routes[$path])) {
    $route = $routes[$path];
    [$controllerName, $method] = explode('@', $route['controller']);
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

    $stack = new MiddlewareStack();
    foreach ($route['middleware'] ?? [] as $middleware) {
        $stack->add($middleware);
    }

    $stack->run(function() use ($method, $pdo) {
        $method($pdo);
    });
} else {
    redirect('/not-found');
}