<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 08-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Validation;

/**
 * 数据验证器
 */
class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];
    protected array $customMessages = [];
    protected static array $customRules = [];

    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
    }

    /**
     * 执行验证
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = $this->parseRules($ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->validateRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * 解析规则字符串
     */
    protected function parseRules($ruleString): array
    {
        if (is_array($ruleString)) {
            return $ruleString;
        }

        return array_map('trim', explode('|', $ruleString));
    }

    /**
     * 验证单个规则
     */
    protected function validateRule(string $field, $value, string $rule): void
    {
        // 解析规则和参数 (例如: "max:100" => ['max', '100'])
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        // 如果字段为空且不是 required 或 required_if 规则，跳过验证
        if ($this->isEmpty($value) && !in_array($ruleName, ['required', 'requiredIf'])) {
            return;
        }

        // 检查自定义规则
        if (isset(self::$customRules[$ruleName])) {
            $callback = self::$customRules[$ruleName];
            if (!$callback($value, $parameter, $this->data)) {
                $this->addError($field, $ruleName, $parameter);
            }
            return;
        }

        // 内置规则
        $method = 'validate' . ucfirst($ruleName);
        if (method_exists($this, $method)) {
            if (!$this->$method($value, $parameter)) {
                $this->addError($field, $ruleName, $parameter);
            }
        }
    }

    /**
     * 添加错误信息
     */
    protected function addError(string $field, string $rule, $parameter = null): void
    {
        $key = "{$field}.{$rule}";

        if (isset($this->customMessages[$key])) {
            $message = $this->customMessages[$key];
        } else {
            $message = $this->getDefaultMessage($field, $rule, $parameter);
        }

        $this->errors[$field][] = $message;
    }

    /**
     * 获取默认错误信息
     */
    protected function getDefaultMessage(string $field, string $rule, $parameter = null): string
    {
        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'url' => "The {$field} must be a valid URL.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'string' => "The {$field} must be a string.",
            'array' => "The {$field} must be an array.",
            'boolean' => "The {$field} must be true or false.",
            'min' => "The {$field} must be at least {$parameter}.",
            'max' => "The {$field} must not be greater than {$parameter}.",
            'between' => "The {$field} must be between {$parameter}.",
            'in' => "The selected {$field} is invalid.",
            'notIn' => "The selected {$field} is invalid.",
            'regex' => "The {$field} format is invalid.",
            'alpha' => "The {$field} may only contain letters.",
            'alphaNum' => "The {$field} may only contain letters and numbers.",
            'alphaDash' => "The {$field} may only contain letters, numbers, dashes and underscores.",
            'phone' => "The {$field} must be a valid phone number.",
            'username' => "The {$field} must be a valid username.",
            'date' => "The {$field} is not a valid date.",
            'confirmed' => "The {$field} confirmation does not match.",
            'same' => "The {$field} and {$parameter} must match.",
            'different' => "The {$field} and {$parameter} must be different.",
            'requiredIf' => "The {$field} field is required when {$parameter}.",
        ];

        return $messages[$rule] ?? "The {$field} field is invalid.";
    }

    /**
     * 检查值是否为空
     */
    protected function isEmpty($value): bool
    {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }

    // ==================== 内置验证规则 ====================

    protected function validateRequired($value): bool
    {
        return !$this->isEmpty($value);
    }

    protected function validateRequiredIf($value, $parameter): bool
    {
        // 参数格式: "field,value" 例如: "submitStep,2"
        if (!$parameter) {
            return true;
        }

        [$fieldName, $fieldValue] = explode(',', $parameter, 2);
        $fieldName = trim($fieldName);
        $fieldValue = trim($fieldValue);

        // 如果指定字段的值与条件值匹配，则当前字段必填
        if (isset($this->data[$fieldName]) && (string)$this->data[$fieldName] === $fieldValue) {
            return !$this->isEmpty($value);
        }

        // 如果条件不匹配，则验证通过
        return true;
    }

    protected function validateEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateNumeric($value): bool
    {
        return is_numeric($value);
    }

    protected function validateInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateString($value): bool
    {
        return is_string($value);
    }

    protected function validateArray($value): bool
    {
        return is_array($value);
    }

    protected function validateBoolean($value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    protected function validateMin($value, $min): bool
    {
        if (is_numeric($value)) {
            return $value >= $min;
        }
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        if (is_array($value)) {
            return count($value) >= $min;
        }
        return false;
    }

    protected function validateMax($value, $max): bool
    {
        if (is_numeric($value)) {
            return $value <= $max;
        }
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        if (is_array($value)) {
            return count($value) <= $max;
        }
        return false;
    }

    protected function validateBetween($value, $range): bool
    {
        [$min, $max] = explode(',', $range);
        return $this->validateMin($value, $min) && $this->validateMax($value, $max);
    }

    protected function validateIn($value, $list): bool
    {
        $values = explode(',', $list);
        // Convert both value and array values to proper types for comparison
        if (is_numeric($value)) {
            $value = $value + 0; // Convert to int or float
            $values = array_map(function($v) {
                return is_numeric($v) ? $v + 0 : $v;
            }, $values);
        }
        return in_array($value, $values, true);
    }

    protected function validateNotIn($value, $list): bool
    {
        return !$this->validateIn($value, $list);
    }

    protected function validateRegex($value, $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    protected function validateAlpha($value): bool
    {
        return preg_match('/^[a-zA-Z]+$/', $value) === 1;
    }

    protected function validateAlphaNum($value): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    protected function validateAlphaDash($value): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
    }

    protected function validatePhone($value): bool
    {
        // 支持多种电话格式
        $pattern = '/^[\d\s\-\+\(\)]+$/';
        return preg_match($pattern, $value) === 1 && strlen(preg_replace('/\D/', '', $value)) >= 10;
    }

    protected function validateUsername($value): bool
    {
        // 用户名：3-20个字符，字母、数字、下划线、连字符
        return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $value) === 1;
    }

    protected function validateDate($value): bool
    {
        return strtotime($value) !== false;
    }

    protected function validateConfirmed($value): bool
    {
        // 需要在 data 中有对应的 {field}_confirmation 字段
        return true; // 这个需要在 validateRule 中特殊处理
    }

    protected function validateSame($value, $otherField): bool
    {
        return isset($this->data[$otherField]) && $value === $this->data[$otherField];
    }

    protected function validateDifferent($value, $otherField): bool
    {
        return !isset($this->data[$otherField]) || $value !== $this->data[$otherField];
    }

    // ==================== 公共方法 ====================

    /**
     * 获取所有错误
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * 获取第一个错误信息
     */
    public function firstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }

    /**
     * 注册自定义验证规则
     */
    public static function extend(string $rule, callable $callback): void
    {
        self::$customRules[$rule] = $callback;
    }

    /**
     * 快捷验证方法
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }
}
