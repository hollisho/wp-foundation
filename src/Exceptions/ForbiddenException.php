<?php

namespace WPFoundation\Exceptions;

use WPFoundation\Http\ResponseCode;

/**
 * 禁止访问异常
 */
class ForbiddenException extends ApiException
{
    public function __construct(
        string $message = '权限不足',
        int $code = ResponseCode::FORBIDDEN
    ) {
        parent::__construct($message, $code, 403);
    }

    /**
     * 创建禁止访问异常
     */
    public static function make(string $message = '权限不足'): self
    {
        return new static($message);
    }
}
