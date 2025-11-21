<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router\Middleware;

use WPFoundation\Http\Request;
use WPFoundation\Http\Response;

/**
 * 管理员权限中间件
 */
class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if (!current_user_can('manage_options')) {
            return Response::forbidden('需要管理员权限');
        }

        return $next($request);
    }
}
