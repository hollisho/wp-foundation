<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 08-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Http;

use WPFoundation\Exceptions\ValidationException;
use WPFoundation\Validation\Validator;

/**
 * Form Request 基类
 * 用于封装请求验证逻辑
 */
abstract class FormRequest
{
    protected Request $request;
    protected array $validatedData = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 定义验证规则
     * 子类必须实现此方法
     */
    abstract public function rules(): array;

    /**
     * 定义自定义错误消息（可选）
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 执行验证
     * 
     * @throws ValidationException
     */
    public function validate(): array
    {
        $validator = new Validator(
            $this->request->all(),
            $this->rules(),
            $this->messages()
        );

        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }

        $this->validatedData = $this->request->all();
        return $this->validatedData;
    }

    /**
     * 获取验证后的数据
     */
    public function validated(): array
    {
        if (empty($this->validatedData)) {
            $this->validate();
        }

        return $this->validatedData;
    }

    /**
     * 获取单个验证后的值
     */
    public function get(string $key, $default = null)
    {
        $validated = $this->validated();
        return $validated[$key] ?? $default;
    }

    /**
     * 获取原始 Request 对象
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * 代理到 Request 对象的方法
     */
    public function __call($method, $arguments)
    {
        return $this->request->$method(...$arguments);
    }

    /**
     * 静态工厂方法
     */
    public static function createFrom(Request $request)
    {
        return new static($request);
    }
}
