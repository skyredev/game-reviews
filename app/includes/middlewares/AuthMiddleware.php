<?php

require_once __DIR__ . '/MiddlewareInterface.php';
require_once __DIR__ . '/../services/auth.php';
require_once __DIR__ . '/../services/redirect.php';

class AuthMiddleware implements MiddlewareInterface {
    private string $type; // 'guest', 'user', 'admin'

    public function __construct(string $type = 'user') {
        $this->type = $type;
    }

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

