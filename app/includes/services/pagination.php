<?php

/**
 * Pagination state management functions
 * 
 * @package App\Includes\Services\Pagination
 */

/**
 * Save pagination parameters to session
 * 
 * @param string $key Unique key for pagination state (e.g., 'games', 'admin')
 * @param array $params Parameters to save (e.g., ['sort' => 'date_desc', 'page' => 3])
 */
function savePaginationState(string $key, array $params): void {
    if (!isset($_SESSION['pagination_states'])) {
        $_SESSION['pagination_states'] = [];
    }
    $_SESSION['pagination_states'][$key] = $params;
}

/**
 * Get pagination parameters from session
 * 
 * @param string $key Unique key for pagination state
 * @return array Saved parameters or empty array
 */
function getPaginationState(string $key): array {
    return $_SESSION['pagination_states'][$key] ?? [];
}

/**
 * Build URL with pagination parameters
 * 
 * @param string $baseUrl Base URL path without query (e.g., '/games' or '/user')
 * @param string $key Pagination state key
 * @param array $overrideParams Parameters to override saved state (e.g., ['page' => 2, 'id' => 11])
 * @return string URL with query parameters
 */
function buildPaginationUrl(string $baseUrl, string $key, array $overrideParams = []): string {
    $savedParams = getPaginationState($key);
    $params = array_merge($savedParams, $overrideParams);
    
    // Parse existing query parameters from baseUrl (for cases like /user?id=11)
    $urlParts = parse_url($baseUrl);
    $basePath = $urlParts['path'] ?? $baseUrl;
    $existingParams = [];
    
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $existingParams);
    }
    
    // Merge existing params with pagination params (pagination params take precedence)
    $params = array_merge($existingParams, $params);
    
    // Remove page param if it is 1 (default)
    if (isset($params['page']) && $params['page'] == 1) {
        unset($params['page']);
    }
    
    if (empty($params)) {
        return $basePath;
    }
    
    return $basePath . '?' . http_build_query($params);
}

/**
 * Update pagination state from current request
 * 
 * @param string $key Pagination state key
 * @param array $allowedParams List of allowed parameter names
 */
function updatePaginationState(string $key, array $allowedParams = ['page', 'sort']): void {
    $savedParams = getPaginationState($key);
    $params = [];
    
    foreach ($allowedParams as $param) {
        if (isset($_GET[$param])) {
            $params[$param] = $_GET[$param];
        } elseif (isset($savedParams[$param])) {
            if ($param === 'page') {
                continue;
            }
            $params[$param] = $savedParams[$param];
        }
    }
    
    savePaginationState($key, $params);
}

