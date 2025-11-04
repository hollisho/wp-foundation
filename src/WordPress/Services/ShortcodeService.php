<?php

namespace WPFoundation\WordPress\Services;

/**
 * 短代码服务
 * 封装 WordPress Shortcode API
 */
class ShortcodeService
{
    protected array $shortcodes = [];

    /**
     * 注册短代码
     */
    public function register(string $tag, callable $callback): self
    {
        $this->shortcodes[$tag] = $callback;
        return $this;
    }

    /**
     * 批量注册短代码
     */
    public function registerMultiple(array $shortcodes): self
    {
        foreach ($shortcodes as $tag => $callback) {
            $this->register($tag, $callback);
        }
        return $this;
    }

    /**
     * 执行注册
     */
    public function registerAll(): void
    {
        foreach ($this->shortcodes as $tag => $callback) {
            add_shortcode($tag, $callback);
        }
    }

    /**
     * 移除短代码
     */
    public function remove(string $tag): void
    {
        remove_shortcode($tag);
        unset($this->shortcodes[$tag]);
    }

    /**
     * 检查短代码是否存在
     */
    public function exists(string $tag): bool
    {
        return shortcode_exists($tag);
    }

    /**
     * 检查是否已配置
     */
    public function has(string $tag): bool
    {
        return isset($this->shortcodes[$tag]);
    }
}
