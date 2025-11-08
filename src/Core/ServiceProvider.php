<?php

namespace WPFoundation\Core;

/**
 * 服务提供者基类
 * 所有服务提供者都应继承此类
 */
abstract class ServiceProvider
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 获取容器（Application 本身就是 Container）
     */
    protected function container(): Application
    {
        return $this->app;
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
        return $this->app->config($key, $default);
    }
}
