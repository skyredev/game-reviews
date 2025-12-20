<?php

/**
 * Save pagination parameters to session
 * 
 * @param string $key Unique key for pagination state (e.g., 'games', 'admin_users')
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
 * @param string $baseUrl Base URL (e.g., '/games')
 * @param string $key Pagination state key
 * @param array $overrideParams Parameters to override saved state
 * @return string URL with query parameters
 */
function buildPaginationUrl(string $baseUrl, string $key, array $overrideParams = []): string {
    $savedParams = getPaginationState($key);
    $params = array_merge($savedParams, $overrideParams);
    
    // Remove page/page params if they are 1 (default)
    foreach (['page', 'users_page', 'games_page'] as $pageParam) {
        if (isset($params[$pageParam]) && $params[$pageParam] == 1) {
            unset($params[$pageParam]);
        }
    }
    
    if (empty($params)) {
        return $baseUrl;
    }
    
    return $baseUrl . '?' . http_build_query($params);
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
            // Keep saved param if not in current GET
            $params[$param] = $savedParams[$param];
        }
    }
    
    if (!empty($params)) {
        savePaginationState($key, $params);
    }
}

