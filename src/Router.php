<?php

declare(strict_types=1);

namespace App;

/**
 * Minimal regex-based front-controller router.
 *
 * Routes are registered with get() / post() and matched against the
 * REQUEST_METHOD + PATH_INFO (or REQUEST_URI stripped of query string).
 *
 * Supports a hidden _method field to tunnel PUT/DELETE through HTML forms.
 *
 * Named segments in patterns like {id} are passed as indexed arguments to
 * the handler callable.
 *
 * Usage:
 *   $router = new Router();
 *   $router->get('/departments',          [DepartmentController::class, 'index']);
 *   $router->get('/departments/{id}',     [DepartmentController::class, 'show']);
 *   $router->post('/departments',         [DepartmentController::class, 'store']);
 *   $router->dispatch();
 */
class Router
{
    /** @var array<string, array<array{pattern: string, handler: callable|array}>> */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|array $handler): void
    {
        $pattern = $this->pathToRegex($path);
        $this->routes[$method][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    /**
     * Match the current request and invoke the handler, or send a 404.
     */
    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Allow _method override for PUT/DELETE via POST forms or JSON bodies
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? null;
            if (in_array(strtoupper((string) $override), ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = strtoupper($override);
            }
        }

        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Normalise trailing slash (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                // Named captures → positional args (skip index 0 = full match)
                $params = array_values(array_filter(
                    $matches,
                    fn($k) => !is_int($k),
                    ARRAY_FILTER_USE_KEY
                ));

                $this->invoke($route['handler'], $params);
                return;
            }
        }

        $this->notFound();
    }

    private function invoke(callable|array $handler, array $params): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method(...$params);
        } else {
            $handler(...$params);
        }
    }

    private function pathToRegex(string $path): string
    {
        // Escape everything except our {placeholder} tokens
        $escaped = preg_quote($path, '#');
        // Replace \{name\} with a named capture group
        $pattern = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^/]+)', $escaped);
        return '#^' . $pattern . '$#';
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 — Page Not Found</h1>';
    }
}
