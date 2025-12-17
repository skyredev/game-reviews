<?php

interface MiddlewareInterface {
    /**
     * Handle the request and pass to the next middleware
     * 
     * @param callable $next Next middleware or controller
     */
    public function handle(callable $next): void;
}

