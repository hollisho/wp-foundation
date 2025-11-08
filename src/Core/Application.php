<?php

namespace WPFoundation\Core;

/**
 * 应用引导类
 * 负责管理整个应用的生命周期
 * 继承 Container，提供统一的容器访问接口
 */
class Application extends Container
{
    protected static ?Application $instance = null;
    protected array $providers = [];
    protected array $bootedProviders = [];
    protected string $basePath;
    protected array $config = [];

    public function __construct(string $basePath = '')
    {
        parent::__construct();

        $this->basePath = $basePath ?: getcwd();
        $this->registerBaseBindings();

        // 设置静态实例
        static::$instance = $this;
    }

    /**
     * 获取应用单例实例
     */
    public static function getInstance(): ?Application
    {
        return static::$instance;
    }

    /**
     * 设置应用实例（用于测试或特殊场景）
     */
    public static function setInstance(?Application $app): void
    {
        static::$instance = $app;
    }

    /**
     * 注册基础绑定
     */
    protected function registerBaseBindings(): void
    {
        $this->singleton(Application::class, fn() => $this);
        $this->singleton('app', fn() => $this);
    }

    /**
     * 注册服务提供者
     */
    public function register($provider): self
    {
        if (is_string($provider)) {
            $provider = new $provider($this);
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
}
