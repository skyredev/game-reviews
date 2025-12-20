<?php

/**
 * Authentication middleware
 * 
 * @package App\Includes\Middlewares
 */

require_once __DIR__ . '/MiddlewareInterface.php';
require_once __DIR__ . '/../services/auth.php';
require_once __DIR__ . '/../services/redirect.php';

/**
 * Middleware for authentication and authorization
 */
class AuthMiddleware implements MiddlewareInterface {
    private string $type; // 'guest', 'user', 'admin'

    /**
     * Constructor
     * 
     * @param string $type Auth type: 'guest', 'user', or 'admin'
     */
    public function __construct(string $type = 'user') {
        $this->type = $type;
    }

    /**
     * Handle the request - check authentication
     * 
     * @param callable $next Next middleware or controller
     * @return void
     */
    public function handle(callable $next): void {
        switch ($this->type) {
            case 'guest':
                requireGuest();
                break;

            case 'user':
                requireUser();
                break;

            case 'admin':
                requireAdmin();
                break;
        }

        // Auth check passed, continue
        $next();
    }
}

