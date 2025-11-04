<?php

namespace WPFoundation\Exceptions;

use Exception;
use WPFoundation\Http\ResponseCode;

/**
 * API 异常基类
 */
class ApiException extends Exception
{
    protected int $statusCode;
    protected $data;

    public function __construct(
        string $message = '',
        int $code = ResponseCode::ERROR,
        int $statusCode = 400,
        $data = null,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 获取额外数据
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * 设置额外数据
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }
}
