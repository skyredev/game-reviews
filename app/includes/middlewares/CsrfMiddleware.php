<?php

/**
 * CSRF protection middleware
 * 
 * @package App\Includes\Middlewares
 */

require_once __DIR__ . '/MiddlewareInterface.php';
require_once __DIR__ . '/../services/csrf.php';

/**
 * Middleware for CSRF token validation
 */
class CsrfMiddleware implements MiddlewareInterface {
    private string $redirectUrl;
    private string $sessionKey;

    /**
     * Constructor
     * 
     * @param string $redirectUrl URL to redirect to on CSRF failure
     * @param string $sessionKey Session key prefix for errors
     */
    public function __construct(string $redirectUrl, string $sessionKey = 'validation') {
        $this->redirectUrl = $redirectUrl;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Handle the request - validate CSRF token
     * 
     * @param callable $next Next middleware or controller
     * @return void
     */
    public function handle(callable $next): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $next();
            return;
        }

        $token = $_POST['csrf_token'] ?? '';

        if (!validateCsrfToken($token)) {
            // Store error and old input for PRG pattern
            $_SESSION[$this->sessionKey . '_errors'] = ['csrf' => ['Neplatný bezpečnostní token.']];
            $_SESSION[$this->sessionKey . '_old'] = $_POST;
            
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

        // CSRF check passed, continue
        $next();
    }
}

