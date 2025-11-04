<?php

namespace WPFoundation\Exceptions;

use WPFoundation\Http\ResponseCode;

/**
 * 资源未找到异常
 */
class NotFoundException extends ApiException
{
    public function __construct(
        string $message = '资源不存在',
        int $code = ResponseCode::NOT_FOUND
    ) {
        parent::__construct($message, $code, 404);
    }

    /**
     * 创建未找到异常
     */
    public static function make(string $resource = '资源'): self
    {
        return new static("{$resource}不存在");
    }
}
