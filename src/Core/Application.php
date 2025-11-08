<?php

namespace WPFoundation\Core;

/**
 * 应用引导类
 * 负责管理整个应用的生命周期
 */
class Application
{
    protected Container $container;
    protected array $providers = [];
    protected array $bootedProviders = [];
    protected string $basePath;
    protected array $config = [];

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath ?: getcwd();
        $this->container = new Container();
        $this->registerBaseBindings();
    }

    /**
     * 注册基础绑定
     */
    protected function registerBaseBindings(): void
    {
        $this->container->singleton(Application::class, fn() => $this);
        $this->container->singleton('app', fn() => $this);
    }

    /**
     * 注册服务提供者
     */
    public function register($provider): self
    {
        if (is_string($provider)) {
            $provider = new $provider($this->container, $this);
        }

        $this->providers[] = $provider;
        $provider->register();

        return $this;
    }

    /**
     * 启动应用
     */
    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            if (method_exists($provider, 'boot')) {
                $provider->boot();
                $this->bootedProviders[] = $provider;
            }
        }
    }

    /**
     * 获取容器
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * 设置配置
     */
    public function configure(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 获取配置
     */
    public function config(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 获取基础路径
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * 魔术方法：从容器中解析服务
     */
    public function make(string $abstract)
    {
        return $this->container->make($abstract);
    }
}
