<?php

namespace App\Core;

// parses the URL and dispatches to the matching controller + method with optional middleware
class Router
{
    private array $routes = [];
    private string $currentGroupMiddleware = '';

    public function get(string $uri, array $handler): self
    {
        $this->routes['GET'][$uri] = [
            'handler' => $handler,
            'middleware' => $this->currentGroupMiddleware,
        ];

        return $this;
    }

    public function post(string $uri, array $handler): self
    {
        $this->routes['POST'][$uri] = [
            'handler' => $handler,
            'middleware' => $this->currentGroupMiddleware,
        ];

        return $this;
    }

    // group routes under a shared middleware
    public function group(string $middleware, callable $callback): void
    {
        $previous = $this->currentGroupMiddleware;
        $this->currentGroupMiddleware = $middleware;
        $callback($this);
        $this->currentGroupMiddleware = $previous;
    }

    // dispatch the request to the correct controller method
    public function dispatch(string $uri, string $method): void
    {
    
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        $method = strtoupper($method);

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo "404 — Route not found";
            return;
        }

        $route = $this->routes[$method][$uri];

        if (!empty($route['middleware'])) {
            $this->runMiddleware($route['middleware']);
        }

        [$controller, $action] = $route['handler'];
        $instance = new $controller();
        $instance->$action();
    }

    // run the named middleware class
    private function runMiddleware(string $middlewareName): void
    {
        $parts = explode(':', $middlewareName);
        $name = $parts[0];
        $params = array_slice($parts, 1);

        if ($name === 'auth') {
            // require the role passed as param
            $role = $params[0] ?? null;
            \App\Middleware\AuthMiddleware::requireAuth($role);
        }
    }
}