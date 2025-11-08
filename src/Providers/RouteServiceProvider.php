<?php

namespace WPFoundation\Providers;

use WPFoundation\Core\ServiceProvider;
use WPFoundation\Hooks\HookRegistrar;
use WPFoundation\Http\Router;

/**
 * 路由服务提供者
 * 负责注册 REST API 路由
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册 Router（不指定默认命名空间，由路由文件自行配置）
        $this->app->singleton(Router::class, function () {
            return new Router($this->app);
        });
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        $hooks = new HookRegistrar($this->app);

        // 在 rest_api_init 钩子中注册路由
        $hooks->addRawAction('rest_api_init', function () {
            $this->loadRoutes();
        });

        // 执行注册
        $hooks->registerAll();
    }

    /**
     * 加载路由文件
     */
    protected function loadRoutes(): void
    {
        $router = $this->app->make(Router::class);

        // 加载 API 路由文件
        $routeFile = $this->app->basePath('routes/api.php');

        if (file_exists($routeFile)) {
            // 重要：在 require 之前确保 $router 变量在作用域内
            require $routeFile;
            error_log('Route file loaded: ' . $routeFile);
        } else {
            // 调试：记录文件不存在
            error_log('Route file not found: ' . $routeFile);
        }

        // 注册所有路由
        $router->register();

        // 调试：记录路由注册完成
        error_log('Routes registered successfully');
    }
}
