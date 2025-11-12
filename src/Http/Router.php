<?php

namespace WPFoundation\Http;

use WPFoundation\Core\Container;
use WP_REST_Request;
use WPFoundation\Exceptions\ExceptionHandler;

/**
 * REST API 路由注册器
 */
class Router
{
    protected Container $container;
    protected string $namespace;
    protected array $routes = [];
    protected array $middlewares = [];
    protected string $prefix = '';

    public function __construct(Container $container, string $namespace = 'api/v1')
    {
        $this->container = $container;
        $this->namespace = $namespace;
    }

    /**
     * 注册 GET 路由
     */
    public function get(string $route, string $controller, string $action): self
    {
        return $this->addRoute('GET', $route, $controller, $action);
    }

    /**
     * 注册 POST 路由
     */
    public function post(string $route, string $controller, string $action): self
    {
        return $this->addRoute('POST', $route, $controller, $action);
    }

    /**
     * 注册 PUT 路由
     */
    public function put(string $route, string $controller, string $action): self
    {
        return $this->addRoute('PUT', $route, $controller, $action);
    }

    /**
     * 注册 PATCH 路由
     */
    public function patch(string $route, string $controller, string $action): self
    {
        return $this->addRoute('PATCH', $route, $controller, $action);
    }

    /**
     * 注册 DELETE 路由
     */
    public function delete(string $route, string $controller, string $action): self
    {
        return $this->addRoute('DELETE', $route, $controller, $action);
    }

    /**
     * 注册任意方法路由
     */
    public function any(string $route, string $controller, string $action): self
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $route, $controller, $action);
    }

    /**
     * 添加路由
     */
    protected function addRoute($methods, string $route, string $controller, string $action): self
    {
        // 应用前缀
        $fullRoute = $this->prefix ? $this->prefix . $route : $route;

        $this->routes[] = [
            'methods' => $methods,
            'route' => $fullRoute,
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $this->middlewares,
            'namespace' => $this->namespace, // 保存当前命名空间
        ];

        // 清空临时中间件
        $this->middlewares = [];

        return $this;
    }

    /**
     * 添加中间件（用于下一个路由）
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * 路由组
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousMiddlewares = $this->middlewares;
        $previousNamespace = $this->namespace;
        $previousPrefix = $this->prefix;

        // 应用组中间件
        if (isset($attributes['middleware'])) {
            $this->middleware($attributes['middleware']);
        }

        // 应用组命名空间
        if (isset($attributes['namespace'])) {
            $this->namespace = $attributes['namespace'];
        }

        // 应用组前缀
        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix . $attributes['prefix'];
        }

        // 执行回调
        $callback($this);

        // 恢复之前的状态
        $this->middlewares = $previousMiddlewares;
        $this->namespace = $previousNamespace;
        $this->prefix = $previousPrefix;
    }

    /**
     * 命名空间组（快捷方法）
     */
    public function namespace(string $namespace, callable $callback): self
    {
        $this->group(['namespace' => $namespace], $callback);
        return $this;
    }

    /**
     * 注册所有路由到 WordPress
     */
    public function register(): void
    {
        foreach ($this->routes as $route) {
            // 使用路由保存的命名空间，而不是当前的 $this->namespace
            $namespace = $route['namespace'] ?? $this->namespace;

            register_rest_route($namespace, $route['route'], [
                'methods' => $route['methods'],
                'callback' => $this->createCallback($route),
                'permission_callback' => $this->createPermissionCallback($route),
            ]);
        }
    }

    /**
     * 创建路由回调
     */
    protected function createCallback(array $route): callable
    {
        return function (WP_REST_Request $wpRequest) use ($route) {
            try {
                // 创建 Request 对象
                $request = new Request($wpRequest);

                // 从容器解析 Controller
                $controller = $this->container->make($route['controller']);

                // 调用 Action，注入 Request
                return call_user_func([$controller, $route['action']], $request);
            } catch (\Throwable $e) {
                // 使用异常处理器处理异常
                return $this->handleException($e);
            }
        };
    }

    /**
     * 处理异常
     */
    protected function handleException(\Throwable $exception)
    {
        // 尝试从容器获取异常处理器
        if ($this->container->has(ExceptionHandler::class)) {
            $handler = $this->container->make(ExceptionHandler::class);
            return $handler->handle($exception);
        }

        // 降级处理：返回基本错误响应
        return Response::serverError(
            defined('WP_DEBUG') && WP_DEBUG
                ? $exception->getMessage()
                : '服务器内部错误'
        );
    }

    /**
     * 创建权限回调
     */
    protected function createPermissionCallback(array $route): callable
    {
        return function () use ($route) {
            // 执行中间件
            foreach ($route['middlewares'] as $middleware) {
                if ($middleware === 'auth') {
                    if (!is_user_logged_in()) {
                        return false;
                    }
                } elseif ($middleware === 'admin') {
                    if (!current_user_can('manage_options')) {
                        return false;
                    }
                } elseif (is_callable($middleware)) {
                    if (!$middleware()) {
                        return false;
                    }
                }
            }

            return true;
        };
    }

    /**
     * 设置命名空间
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * 获取命名空间
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
