<?php

/**
 * WP Foundation 辅助函数
 */

if (!function_exists('wp_foundation_app')) {
    /**
     * 获取应用实例
     */
    function wp_foundation_app(): ?\WPFoundation\Core\Application
    {
        return $GLOBALS['wp_foundation_app'] ?? null;
    }
}

if (!function_exists('wp_foundation_container')) {
    /**
     * 获取容器实例
     */
    function wp_foundation_container(): ?\WPFoundation\Core\Container
    {
        $app = wp_foundation_app();
        return $app ? $app->getContainer() : null;
    }
}

if (!function_exists('wp_foundation_make')) {
    /**
     * 从容器中解析服务
     */
    function wp_foundation_make(string $abstract)
    {
        $container = wp_foundation_container();
        return $container ? $container->make($abstract) : null;
    }
}

if (!function_exists('wp_foundation_config')) {
    /**
     * 获取配置值
     */
    function wp_foundation_config(string $key, $default = null)
    {
        $app = wp_foundation_app();
        return $app ? $app->config($key, $default) : $default;
    }
}
