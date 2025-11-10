<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 08-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Http;

use WP_REST_Request;
use WP_User;
use WPFoundation\Exceptions\ValidationException;
use WPFoundation\Validation\Validator;

/**
 * HTTP Request 封装类
 * 统一的请求对象，封装 WP_REST_Request
 */
class Request
{
    protected WP_REST_Request $wpRequest;

    public function __construct(WP_REST_Request $wpRequest)
    {
        $this->wpRequest = $wpRequest;
    }

    /**
     * 获取原始 WP_REST_Request 对象
     */
    public function getWpRequest(): WP_REST_Request
    {
        return $this->wpRequest;
    }

    /**
     * 获取所有参数（合并 URL、Body、Query）
     */
    public function all(): array
    {
        return $this->wpRequest->get_params();
    }

    /**
     * 获取单个参数
     */
    public function get(string $key, $default = null)
    {
        return $this->wpRequest->get_param($key) ?? $default;
    }

    /**
     * 获取 URL 参数
     */
    public function route(string $key, $default = null)
    {
        return $this->wpRequest->get_url_params()[$key] ?? $default;
    }

    /**
     * 获取 Query 参数
     */
    public function query(string $key, $default = null)
    {
        return $this->wpRequest->get_query_params()[$key] ?? $default;
    }

    /**
     * 获取 Body 参数
     */
    public function input(string $key, $default = null)
    {
        return $this->wpRequest->get_body_params()[$key] ?? $default;
    }

    /**
     * 获取 JSON 参数
     */
    public function json(string $key = null, $default = null)
    {
        $params = $this->wpRequest->get_json_params();
        
        if ($key === null) {
            return $params;
        }
        
        return $params[$key] ?? $default;
    }

    /**
     * 获取请求方法
     */
    public function method(): string
    {
        return $this->wpRequest->get_method();
    }

    /**
     * 检查是否为指定方法
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->method()) === strtoupper($method);
    }

    /**
     * 获取请求头
     */
    public function header(string $key, $default = null)
    {
        return $this->wpRequest->get_header($key) ?? $default;
    }

    /**
     * 获取所有请求头
     */
    public function headers(): array
    {
        return $this->wpRequest->get_headers();
    }

    /**
     * 检查参数是否存在
     */
    public function has(string $key): bool
    {
        return $this->wpRequest->has_param($key);
    }

    /**
     * 检查多个参数是否都存在
     */
    public function hasAll(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 只获取指定的参数
     */
    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->get($key);
            }
        }
        return $result;
    }

    /**
     * 排除指定的参数
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    /**
     * 获取当前用户
     */
    public function user(): ?WP_User
    {
        $userId = get_current_user_id();
        return $userId ? get_user_by('id', $userId) : null;
    }

    /**
     * 检查用户是否已登录
     */
    public function isAuthenticated(): bool
    {
        return is_user_logged_in();
    }

    /**
     * 验证参数
     * 
     * @param array $rules 验证规则
     * @param array $messages 自定义错误信息
     * @return array 验证错误（空数组表示验证通过）
     *
     */
    public function validate(array $rules, array $messages = []): array
    {
        $validator = new Validator(
            $this->all(),
            $rules,
            $messages
        );

        if (!$validator->validate()) {
            return $validator->errors();
        }

        return [];
    }

    /**
     * 验证参数并在失败时抛出异常
     * 
     * @param array $rules 验证规则
     * @param array $messages 自定义错误信息
     * @return array 验证通过的数据
     * 
     * @throws ValidationException
     */
    public function validateOrFail(array $rules, array $messages = []): array
    {
        $errors = $this->validate($rules, $messages);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $this->all();
    }
}
