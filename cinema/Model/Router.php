<?php

namespace Model;

class Router
{
    private const PAGE_NOT_FOUND = 'not-found';
    private const CONFIG_NAME = 'routes.json';
    private static ?Router $instance = null;
    private array $routes = [];

    public function __construct()
    {
        if (!$this->load()) {
            throw new \Error('Invalid routes config');
        }
    }

    private function load(): bool
    {
        $jsonString = file_get_contents(CONFIG_DIR . '/' . self::CONFIG_NAME);
        $data = json_decode($jsonString, true);

        if (is_array($data)) {
            $this->routes = $data;
            return true;
        }
        return false;
    }

    private function callControllerMethod(array $routeItem): bool
    {
        $controllerClass = '\Controller\\' . $routeItem['controller'];
        $controllerMethod = @$routeItem['method'];
        if (class_exists($controllerClass) && $controllerMethod) {
            $controller = new $controllerClass();
            if (method_exists($controllerClass, $controllerMethod)) {
                $controller->$controllerMethod();
                return true;
            }
        }
        return false;
    }

    public function processNotFound(): void
    {
        foreach ($this->routes as $route) {
            if (@$route['name'] === self::PAGE_NOT_FOUND) {
                if ($this->callControllerMethod($route)) {
                    return;
                }
            }
        }

        http_response_code(404);
        die;//('Page not found');
    }

    public function route(string $uri): void
    {
        $urlArr = parse_url($uri);

        foreach ($this->routes as $route) {
            if (@$route['route'] === $urlArr['path']) {
                if ($this->callControllerMethod($route)) {
                    return;
                }
            }
        }

        $this->processNotFound();
    }

    public static function redirect(string $to, array $params=[], int $code = 302): never
    {
        if (!empty($params)) {
            $to .= '?' . http_build_query($params);
        }
        header("Location: $to", true, $code);
        exit;
    }

    public function redirectToName(string $name, array $params=[], int $code = 302): never
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                self::redirect($route['route'], $params,$code);
            }
        }

        $this->processNotFound();
    }

    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }
}