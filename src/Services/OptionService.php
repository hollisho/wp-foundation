<?php

namespace WPFoundation\Services;

/**
 * 选项服务
 * 封装 WordPress Options API
 */
class OptionService
{
    protected string $prefix;

    public function __construct(string $prefix = 'wp_foundation_')
    {
        $this->prefix = $prefix;
    }

    /**
     * 设置前缀
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 获取选项
     */
    public function get(string $key, $default = null)
    {
        return get_option($this->prefix . $key, $default);
    }

    /**
     * 设置选项
     */
    public function set(string $key, $value): bool
    {
        return update_option($this->prefix . $key, $value);
    }

    /**
     * 批量保存选项
     */
    public function save(array $data): bool
    {
        $success = true;

        foreach ($data as $key => $value) {
            if (!update_option($this->prefix . $key, $value)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 删除选项
     */
    public function delete(string $key): bool
    {
        return delete_option($this->prefix . $key);
    }

    /**
     * 检查选项是否存在
     */
    public function has(string $key): bool
    {
        return get_option($this->prefix . $key) !== false;
    }
}
