<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router\Middleware;

use WPFoundation\Http\Request;

/**
 * 中间件接口
 */
interface MiddlewareInterface
{
    /**
     * 处理请求
     *
     * @param Request $request 请求对象
     * @param callable $next 下一个中间件
     * @return mixed
     */
    public function handle(Request $request, callable $next);
}
