<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router;

use WP_REST_Request;
use WPFoundation\Core\Container;
use WPFoundation\Exceptions\ExceptionHandler;
use ReflectionMethod;
use WPFoundation\Http\Request;
use WPFoundation\Http\Response;
use WPFoundation\Support\Router\Middleware\MiddlewarePipeline;
use WPFoundation\Support\Router\Middleware\MiddlewareRegistry;

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
    protected array $groupMiddlewares = [];
    protected MiddlewareRegistry $middlewareRegistry;
    protected ParameterResolver $parameterResolver;
    protected array $beforeMiddleware = [];
    protected array $afterMiddleware = [];

    public function __construct(Container $container, string $namespace = 'api/v1')
    {
        $this->container = $container;
        $this->namespace = $namespace;
        $this->middlewareRegistry = new MiddlewareRegistry($container);
        $this->parameterResolver = new ParameterResolver($container);
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

        // 合并当前中间件和组级别的中间件
        $allMiddlewares = array_merge($this->groupMiddlewares, $this->middlewares);

        $this->routes[] = [
            'methods' => $methods,
            'route' => $fullRoute,
            'controller' => $controller,
            'action' => $action,
            'middlewares' => $allMiddlewares,
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
     * 注册全局前置中间件
     */
    public function before(callable $callback): self
    {
        $this->beforeMiddleware[] = $callback;
        return $this;
    }

    /**
     * 注册全局后置中间件
     */
    public function after(callable $callback): self
    {
        $this->afterMiddleware[] = $callback;
        return $this;
    }

    /**
     * 注册自定义中间件别名
     */
    public function registerMiddleware(string $alias, $middleware): self
    {
        $this->middlewareRegistry->register($alias, $middleware);
        return $this;
    }

    /**
     * 获取中间件注册表
     */
    public function getMiddlewareRegistry(): MiddlewareRegistry
    {
        return $this->middlewareRegistry;
    }

    /**
     * 路由组
     */
    public function group(array $attributes, callable $callback): void
    {
        // 保存当前状态
        $previousGroupMiddlewares = $this->groupMiddlewares;
        $previousNamespace = $this->namespace;
        $previousPrefix = $this->prefix;

        // 构建新的组中间件数组
        $newGroupMiddlewares = $this->groupMiddlewares;

        // 应用组中间件到组级别的中间件数组
        if (isset($attributes['middleware'])) {
            if (is_array($attributes['middleware'])) {
                $newGroupMiddlewares = array_merge($newGroupMiddlewares, $attributes['middleware']);
            } else {
                $newGroupMiddlewares[] = $attributes['middleware'];
            }
        }

        // 将链式调用的临时中间件（如 $router->middleware('auth')->group()）合并到组中间件
        if (!empty($this->middlewares)) {
            $newGroupMiddlewares = array_merge($newGroupMiddlewares, $this->middlewares);
            // 清空临时中间件，避免影响后续路由
            $this->middlewares = [];
        }

        // 应用组命名空间
        if (isset($attributes['namespace'])) {
            $this->namespace = $attributes['namespace'];
        }

        // 应用组前缀
        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix . $attributes['prefix'];
        }

        // 设置新的组中间件
        $this->groupMiddlewares = $newGroupMiddlewares;
        
        // 执行回调
        $callback($this);

        // 恢复之前的状态
        $this->groupMiddlewares = $previousGroupMiddlewares;
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

            // 转换路由参数格式：{id} => (?P<id>[\d]+)
            $wpRoute = preg_replace('/\{(\w+)\}/', '(?P<$1>[\w\-]+)', $route['route']);

            register_rest_route($namespace, $wpRoute, [
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

                // 执行前置中间件
                foreach ($this->beforeMiddleware as $middleware) {
                    $result = $middleware($request);
                    if ($result !== null) {
                        return $result;
                    }
                }

                // 创建中间件管道
                $pipeline = new MiddlewarePipeline($this->middlewareRegistry);
                $pipeline->pipe($route['middlewares']);

                // 定义最终的控制器处理逻辑
                $destination = function (Request $request) use ($route, $wpRequest) {
                    return $this->executeController($route, $request, $wpRequest);
                };

                // 通过中间件管道执行
                $response = $pipeline->handle($request, $destination);

                // 执行后置中间件
                foreach ($this->afterMiddleware as $middleware) {
                    $response = $middleware($request, $response) ?? $response;
                }

                return $response;
            } catch (\Throwable $e) {
                // 使用异常处理器处理异常
                return $this->handleException($e);
            }
        };
    }

    /**
     * 执行控制器方法
     */
    protected function executeController(array $route, Request $request, WP_REST_Request $wpRequest)
    {
        // 从容器解析 Controller
        $controller = $this->container->make($route['controller']);

        // 使用反射获取方法信息
        $method = new ReflectionMethod($controller, $route['action']);

        // 解析方法参数
        $args = $this->parameterResolver->resolve(
            $method,
            $wpRequest->get_url_params(),
            $request
        );

        // 调用控制器方法
        return $method->invokeArgs($controller, $args);
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
     * 注意：权限检查现在主要在中间件管道中处理
     * 这里保留基本的权限检查以兼容 WordPress REST API
     */
    protected function createPermissionCallback(array $route): callable
    {
        return function () use ($route) {
            // 执行中间件权限检查
            foreach ($route['middlewares'] as $middleware) {
                // 内置中间件：auth
                if ($middleware === 'auth') {
                    if (!is_user_logged_in()) {
                        return false;
                    }
                }
                // 内置中间件：admin
                elseif ($middleware === 'admin') {
                    if (!current_user_can('manage_options')) {
                        return false;
                    }
                }
                // 支持可调用函数作为中间件（向后兼容）
                elseif (is_callable($middleware)) {
                    if (!$middleware()) {
                        return false;
                    }
                }
            }

            // 默认允许通过，让中间件管道处理详细的权限逻辑
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
