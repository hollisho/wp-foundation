<?php

namespace WPFoundation\Exceptions;

use WPFoundation\Http\Response;
use WPFoundation\Http\ResponseCode;
use Throwable;
use WP_REST_Response;

/**
 * 统一异常处理器
 * 
 * 捕获并处理所有异常，返回统一格式的响应
 */
class ExceptionHandler
{
    protected $logger;
    protected bool $debug;

    public function __construct($logger = null, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;
    }

    /**
     * 处理异常
     * 
     * @param Throwable $exception
     * @return WP_REST_Response
     */
    public function handle(Throwable $exception): WP_REST_Response
    {
        // 记录日志
        $this->logException($exception);

        // 根据异常类型返回不同响应
        if ($exception instanceof ApiException) {
            return $this->handleApiException($exception);
        }

        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }

        if ($exception instanceof NotFoundException) {
            return $this->handleNotFoundException($exception);
        }

        if ($exception instanceof UnauthorizedException) {
            return $this->handleUnauthorizedException($exception);
        }

        if ($exception instanceof ForbiddenException) {
            return $this->handleForbiddenException($exception);
        }

        // 默认处理
        return $this->handleGenericException($exception);
    }

    /**
     * 处理 API 异常
     */
    protected function handleApiException(ApiException $exception): WP_REST_Response
    {
        return Response::error(
            $exception->getMessage(),
            $exception->getCode() ?: ResponseCode::ERROR,
            $this->getExceptionData($exception),
            $exception->getStatusCode()
        );
    }

    /**
     * 处理验证异常
     */
    protected function handleValidationException(ValidationException $exception): WP_REST_Response
    {
        return Response::validationError(
            $exception->getErrors(),
            $exception->getMessage()
        );
    }

    /**
     * 处理未找到异常
     */
    protected function handleNotFoundException(NotFoundException $exception): WP_REST_Response
    {
        return Response::notFound($exception->getMessage());
    }

    /**
     * 处理未授权异常
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): WP_REST_Response
    {
        return Response::unauthorized($exception->getMessage());
    }

    /**
     * 处理禁止访问异常
     */
    protected function handleForbiddenException(ForbiddenException $exception): WP_REST_Response
    {
        return Response::forbidden($exception->getMessage());
    }

    /**
     * 处理通用异常
     */
    protected function handleGenericException(Throwable $exception): WP_REST_Response
    {
        $message = $this->debug 
            ? $exception->getMessage() 
            : '服务器内部错误';

        return Response::serverError($message);
    }

    /**
     * 记录异常日志
     */
    protected function logException(Throwable $exception): void
    {
        if (!$this->logger) {
            return;
        }

        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $this->formatTrace($exception->getTrace()),
        ];

        // 根据异常类型使用不同的日志级别
        if ($exception instanceof ApiException) {
            $this->logger->warning('API Exception: ' . $exception->getMessage(), $context);
        } elseif ($exception instanceof ValidationException) {
            $this->logger->info('Validation Exception: ' . $exception->getMessage(), $context);
        } elseif ($exception instanceof NotFoundException) {
            $this->logger->info('Not Found Exception: ' . $exception->getMessage(), $context);
        } elseif ($exception instanceof UnauthorizedException) {
            $this->logger->warning('Unauthorized Exception: ' . $exception->getMessage(), $context);
        } else {
            $this->logger->error('Unhandled Exception: ' . $exception->getMessage(), $context);
        }
    }

    /**
     * 格式化堆栈跟踪
     */
    protected function formatTrace(array $trace): array
    {
        return array_slice(array_map(function ($item) {
            return [
                'file' => $item['file'] ?? 'unknown',
                'line' => $item['line'] ?? 0,
                'function' => $item['function'] ?? 'unknown',
                'class' => $item['class'] ?? null,
            ];
        }, $trace), 0, 5); // 只保留前 5 层
    }

    /**
     * 获取异常数据（调试模式）
     */
    protected function getExceptionData(Throwable $exception)
    {
        if (!$this->debug) {
            return null;
        }

        return [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => array_slice($exception->getTrace(), 0, 3),
        ];
    }

    /**
     * 设置日志记录器
     */
    public function setLogger($logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * 设置调试模式
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }
}
