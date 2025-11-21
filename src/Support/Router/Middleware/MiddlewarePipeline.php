<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router\Middleware;

use WPFoundation\Http\Request;

/**
 * 中间件管道
 * 实现洋葱模型的中间件处理流程
 */
class MiddlewarePipeline
{
    private array $middlewares = [];
    private MiddlewareRegistry $registry;

    public function __construct(MiddlewareRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * 添加中间件到管道
     */
    public function pipe($middleware): self
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    /**
     * 执行中间件管道
     *
     * @param Request $request 请求对象
     * @param callable $destination 最终目标处理函数
     * @return mixed
     */
    public function handle(Request $request, callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => fn($req) => $this->executeMiddleware($middleware, $req, $next),
            $destination
        );

        return $pipeline($request);
    }

    /**
     * 执行单个中间件
     */
    private function executeMiddleware($middleware, Request $request, callable $next)
    {
        // 字符串别名，从注册表解析
        if (is_string($middleware)) {
            $middleware = $this->registry->resolve($middleware);
        }

        // 可调用函数
        if (is_callable($middleware)) {
            return $middleware($request, $next);
        }

        // 中间件对象
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->handle($request, $next);
        }

        throw new \InvalidArgumentException('无效的中间件类型');
    }

    /**
     * 清空中间件
     */
    public function clear(): void
    {
        $this->middlewares = [];
    }

    /**
     * 获取所有中间件
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
