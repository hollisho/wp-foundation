<?php

namespace WPFoundation\Core;

/**
 * 服务提供者基类
 * 所有服务提供者都应继承此类
 */
abstract class ServiceProvider
{
    protected Container $container;
    protected ?Application $app;

    public function __construct(Container $container, ?Application $app = null)
    {
        $this->container = $container;
        $this->app = $app;
    }

    /**
     * 注册服务到容器
     */
    abstract public function register(): void;

    /**
     * 启动服务（注册钩子等）
     */
    public function boot(): void
    {
        // 子类可选实现
    }

    /**
     * 获取配置
     */
    protected function config(string $key, $default = null)
    {
        return $this->app ? $this->app->config($key, $default) : $default;
    }
}
