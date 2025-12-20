<?php

/**
 * Middleware stack for chaining multiple middlewares
 * 
 * @package App\Includes\Middlewares
 */
class MiddlewareStack {
    private array $middlewares = [];

    /**
     * Add middleware to stack
     * 
     * @param MiddlewareInterface|callable $middleware Middleware instance or callable
     * @return self
     */
    public function add($middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Run middleware stack with controller
     * 
     * @param callable $controller Controller function to execute after all middlewares
     * @return void
     */
    public function run(callable $controller): void {
        $this->createNext(0, $controller)();
    }

    /**
     * Create next middleware callback
     * 
     * @param int $index Current middleware index
     * @param callable $controller Controller function
     * @return callable Next middleware callback
     */
    private function createNext(int $index, callable $controller): callable {
        return function() use ($index, $controller) {
            if ($index < count($this->middlewares)) {
                $middleware = $this->middlewares[$index];
                if (is_object($middleware) && method_exists($middleware, 'handle')) {
                    $middleware->handle($this->createNext($index + 1, $controller));
                } elseif (is_callable($middleware)) {
                    $middleware($this->createNext($index + 1, $controller));
                } else {
                    $this->createNext($index + 1, $controller)();
                }
            } else {
                $controller();
            }
        };
    }
}
