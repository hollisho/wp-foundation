<?php

namespace WPFoundation\Exceptions;

use WPFoundation\Http\ResponseCode;

/**
 * 验证异常
 */
class ValidationException extends ApiException
{
    protected array $errors;

    public function __construct(
        array $errors,
        string $message = '参数验证失败',
        int $code = ResponseCode::VALIDATION_ERROR
    ) {
        parent::__construct($message, $code, 422, $errors);
        $this->errors = $errors;
    }

    /**
     * 获取验证错误
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 创建验证异常
     */
    public static function withErrors(array $errors, string $message = '参数验证失败'): self
    {
        return new static($errors, $message);
    }
}
