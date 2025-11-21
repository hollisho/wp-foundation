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
 * 认证中间件
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next)
    {
        if (!is_user_logged_in()) {
            return Response::unauthorized('Login required');
        }

        return $next($request);
    }
}
