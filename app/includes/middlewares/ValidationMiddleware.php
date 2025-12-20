<?php

/**
 * Form validation middleware
 * 
 * @package App\Includes\Middlewares
 */

require_once __DIR__ . '/MiddlewareInterface.php';
require_once __DIR__ . '/../services/Validator.php';
require_once __DIR__ . '/../services/redirect.php';

/**
 * Middleware for form data validation
 */
class ValidationMiddleware implements MiddlewareInterface {
    private array $rules;
    private string $redirectUrl;
    private string $sessionKey;

    /**
     * Constructor
     * 
     * @param array $rules Validation rules
     * @param string $redirectUrl URL to redirect to on validation failure
     * @param string $sessionKey Session key prefix for errors and old input
     */
    public function __construct(array $rules, string $redirectUrl, string $sessionKey = 'validation') {
        $this->rules = $rules;
        $this->redirectUrl = $redirectUrl;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Handle the request - validate form data
     * 
     * @param callable $next Next middleware or controller
     * @return void
     */
    public function handle(callable $next): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $next();
            return;
        }

        $validator = new Validator($_POST, $_FILES);
        $errors = $validator->validate($this->rules);

        if (!empty($errors)) {
            $_SESSION[$this->sessionKey . '_errors'] = $errors;
            $_SESSION[$this->sessionKey . '_old'] = excludeSensitiveFields($_POST);
            
            $queryParams = [];
            if (!empty($_SERVER['QUERY_STRING'])) {
                parse_str($_SERVER['QUERY_STRING'], $queryParams);
            }
            if (!empty($_POST['game_id'])) {
                $queryParams['id'] = (int)$_POST['game_id'];
            }
            
            $redirectUrl = $this->redirectUrl;
            if (!empty($queryParams)) {
                $redirectUrl .= '?' . http_build_query($queryParams);
            }
            
            redirect($redirectUrl);
        }

        // Validation passed, continue to next middleware/controller
        $next();
    }
}

