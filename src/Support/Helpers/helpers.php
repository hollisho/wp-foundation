<?php

/**
 * WP Foundation 辅助函数
 */

use WPFoundation\Core\Application;

if (!function_exists('wp_foundation_app')) {
    /**
     * 获取应用实例或从容器中解析服务
     *
     * @param string|null $abstract 要解析的服务类名或别名，为 null 时返回应用实例
     * @param array $parameters 构造参数（暂不支持，预留接口）
     * @return mixed 返回应用实例或解析的服务
     *
     * @example
     * // 获取应用实例
     * $app = wp_foundation_app();
     *
     * // 获取指定服务
     * $logger = wp_foundation_app(LogService::class);
     * $logger = wp_foundation_app('log');
     */
    function wp_foundation_app(?string $abstract = null, array $parameters = [])
    {
        $app = Application::getInstance();

        if (is_null($abstract)) {
            return $app;
        }

        return $app ? $app->make($abstract) : null;
    }
}

if (!function_exists('wp_foundation_container')) {
    /**
     * 获取容器实例（Application 本身就是 Container）
     */
    function wp_foundation_container(): ?\WPFoundation\Core\Container
    {
        return wp_foundation_app();
    }
}

if (!function_exists('wp_foundation_make')) {
    /**
     * 从容器中解析服务
     */
    function wp_foundation_make(string $abstract)
    {
        return wp_foundation_app() ? wp_foundation_app()->make($abstract): null;
    }
}

if (!function_exists('wp_foundation_config')) {
    /**
     * 获取配置值
     */
    function wp_foundation_config(string $key, $default = null)
    {
        return wp_foundation_app() ? wp_foundation_app()->config($key, $default) : $default;
    }
}
