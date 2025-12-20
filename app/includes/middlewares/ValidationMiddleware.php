<?php

require_once __DIR__ . '/MiddlewareInterface.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../services/redirect.php';

class ValidationMiddleware implements MiddlewareInterface {
    private array $rules;
    private string $redirectUrl;
    private string $sessionKey;

    public function __construct(array $rules, string $redirectUrl, string $sessionKey = 'validation') {
        $this->rules = $rules;
        $this->redirectUrl = $redirectUrl;
        $this->sessionKey = $sessionKey;
    }

    public function handle(callable $next): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $next();
            return;
        }

        $validator = new Validator($_POST, $_FILES);
        $errors = $validator->validate($this->rules);

        if (!empty($errors)) {
            // Store errors and old input for PRG pattern
            $_SESSION[$this->sessionKey . '_errors'] = $errors;
            $_SESSION[$this->sessionKey . '_old'] = excludeSensitiveFields($_POST);
            
            // Build redirect URL with query parameters
            $redirectUrl = $this->redirectUrl;
            
            // Preserve query parameters from current request
            $queryParams = [];
            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $queryParams);
            }
            
            // If game_id is in POST, add it to query params
            if (isset($_POST['game_id']) && !empty($_POST['game_id'])) {
                $queryParams['id'] = (int)$_POST['game_id'];
            }
            
            // Build query string
            if (!empty($queryParams)) {
                $queryString = http_build_query($queryParams);
                $redirectUrl .= '?' . $queryString;
            }
            
            // Redirect back
            header('Location: ' . APP_BASE . $redirectUrl);
            exit;
        }

        // Validation passed, continue to next middleware/controller
        $next();
    }
}

