<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router\Middleware;

use WPFoundation\Core\Container;

/**
 * 中间件注册表
 * 管理中间件别名和实例化
 */
class MiddlewareRegistry
{
    private array $middlewares = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->registerDefaultMiddlewares();
    }

    /**
     * 注册默认中间件
     */
    protected function registerDefaultMiddlewares(): void
    {
        $this->middlewares = [
            'auth' => AuthMiddleware::class,
            'admin' => AdminMiddleware::class,
        ];
    }

    /**
     * 注册中间件别名
     *
     * @param string $alias 别名
     * @param string|callable $middleware 中间件类名或可调用对象
     */
    public function register(string $alias, $middleware): void
    {
        $this->middlewares[$alias] = $middleware;
    }

    /**
     * 批量注册中间件
     */
    public function registerMany(array $middlewares): void
    {
        foreach ($middlewares as $alias => $middleware) {
            $this->register($alias, $middleware);
        }
    }

    /**
     * 解析中间件
     *
     * @param string $alias 中间件别名
     * @return MiddlewareInterface|callable
     */
    public function resolve(string $alias)
    {
        if (!isset($this->middlewares[$alias])) {
            throw new \InvalidArgumentException("未知中间件: {$alias}");
        }

        $middleware = $this->middlewares[$alias];

        // 如果是类名，从容器解析
        if (is_string($middleware) && class_exists($middleware)) {
            return $this->container->make($middleware);
        }

        // 直接返回可调用对象
        if (is_callable($middleware)) {
            return $middleware;
        }

        throw new \InvalidArgumentException("无效的中间件配置: {$alias}");
    }

    /**
     * 检查中间件是否已注册
     */
    public function has(string $alias): bool
    {
        return isset($this->middlewares[$alias]);
    }

    /**
     * 获取所有已注册的中间件别名
     */
    public function all(): array
    {
        return array_keys($this->middlewares);
    }
}
