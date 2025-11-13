<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 08-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Exceptions;

use Exception;

/**
 * 验证异常
 */
class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $message = '', int $code = 422)
    {
        $this->errors = $errors;

        // 如果没有提供自定义消息，使用第一个验证错误作为消息
        if (empty($message)) {
            $message = $this->getFirstError() ?: 'Validation failed';
        }

        parent::__construct($message, $code);
    }

    /**
     * 获取所有验证错误
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 获取第一个错误信息
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }

    /**
     * 获取指定字段的错误信息
     *
     * @param string $field 字段名
     * @return array|null 返回该字段的所有错误信息数组，如果不存在则返回 null
     */
    public function getFieldErrors(string $field): ?array
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * 获取指定字段的第一个错误信息
     *
     * @param string $field 字段名
     * @return string|null 返回该字段的第一个错误信息，如果不存在则返回 null
     */
    public function getFieldFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * 检查指定字段是否有错误
     *
     * @param string $field 字段名
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * 获取所有错误信息的扁平化数组
     *
     * @return array 返回所有错误信息的一维数组
     */
    public function getAllMessages(): array
    {
        $messages = [];
        foreach ($this->errors as $fieldErrors) {
            $messages = array_merge($messages, $fieldErrors);
        }
        return $messages;
    }

    /**
     * 获取格式化的错误信息字符串
     *
     * @param string $separator 分隔符，默认为换行符
     * @return string 返回用分隔符连接的所有错误信息
     */
    public function getErrorsAsString(string $separator = "\n"): string
    {
        return implode($separator, $this->getAllMessages());
    }

    /**
     * 获取错误字段列表
     *
     * @return array 返回所有有错误的字段名数组
     */
    public function getErrorFields(): array
    {
        return array_keys($this->errors);
    }

    /**
     * 获取错误数量
     *
     * @return int 返回错误字段的数量
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * 获取所有错误信息的总数量（包括每个字段的多个错误）
     *
     * @return int
     */
    public function getTotalErrorCount(): int
    {
        $count = 0;
        foreach ($this->errors as $fieldErrors) {
            $count += count($fieldErrors);
        }
        return $count;
    }

    /**
     * 转换为数组格式（用于 JSON 响应）
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'first_error' => $this->getFirstError(),
        ];
    }

    /**
     * 转换为 JSON 字符串
     *
     * @param int $options JSON 编码选项
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
