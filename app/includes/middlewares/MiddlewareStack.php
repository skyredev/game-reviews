<?php

class MiddlewareStack {
    private array $middlewares = [];

    public function add($middleware): self {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function run(callable $controller): void {
        $this->createNext(0, $controller)();
    }

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
