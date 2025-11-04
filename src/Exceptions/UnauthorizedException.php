<?php

namespace WPFoundation\Exceptions;

use WPFoundation\Http\ResponseCode;

/**
 * 未授权异常
 */
class UnauthorizedException extends ApiException
{
    public function __construct(
        string $message = '用户未登录',
        int $code = ResponseCode::UNAUTHORIZED
    ) {
        parent::__construct($message, $code, 401);
    }

    /**
     * 创建未授权异常
     */
    public static function make(string $message = '用户未登录'): self
    {
        return new static($message);
    }
}
