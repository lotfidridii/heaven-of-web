<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{0:string,1:string,2:callable|array}> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->routes[] = ['POST', $pattern, $handler];
    }

    /**
     * Patterns use {name} for one path segment or {name:regex} for a custom match,
     * e.g. '/{lang:fr|en|ar}/realisations/{slug}'.
     */
    public function dispatch(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');
        $pathMatched = false;
        foreach ($this->routes as [$m, $pattern, $handler]) {
            if (!preg_match($this->compile($pattern), $path, $matches)) continue;
            $pathMatched = true;
            if ($m !== $method && !($m === 'GET' && $method === 'HEAD')) continue;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            if (is_array($handler)) {
                [$class, $action] = $handler;
                (new $class())->$action(...$params);
            } else {
                $handler(...$params);
            }
            return;
        }
        throw new HttpException($pathMatched ? 405 : 404);
    }

    private function compile(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');
        $regex = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}#',
            fn($m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $pattern
        );
        return '#^' . $regex . '$#u';
    }
}
