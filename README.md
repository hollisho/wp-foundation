# WP Foundation Framework

现代化的 WordPress 插件/主题基础框架，提供依赖注入、服务提供者、REST API 路由、统一异常处理等功能。

## ✨ 核心特性

✅ **依赖注入容器** - 基于 PHP-DI 的自动装配  
✅ **服务提供者** - Laravel 风格的服务注册  
✅ **REST API 路由** - 基于配置文件的路由系统  
✅ **统一 Request/Response** - 标准化的请求响应对象  
✅ **异常处理** - 统一的异常处理和日志记录  
✅ **钩子管理** - 优雅的 WordPress 钩子注册  
✅ **WordPress 服务** - 封装 WP API 的服务类  
✅ **PSR-4 自动加载** - 标准的命名空间  
✅ **性能优化** - 生产环境编译缓存  

## 📦 安装

```bash
composer require hollisho/wp-foundation
```

或手动加载：

```php
require_once __DIR__ . '/vendor/hollisho/wp-foundation/autoload.php';
```

## 🚀 快速开始

### 基本设置

```php
<?php
// my-plugin.php

use WPFoundation\Core\Application;

// 创建应用
$app = new Application(__DIR__);

// 配置应用
$app->configure([
    'prefix' => 'my_plugin_',
    'log_path' => wp_upload_dir()['basedir'] . '/my-plugin-logs',
]);

// 注册服务提供者
$app->register(MyPlugin\Providers\AdminServiceProvider::class);
$app->register(MyPlugin\Providers\RouteServiceProvider::class);

// 启动应用
$app->boot();

return $app;
```

### 创建服务提供者

```php
<?php

namespace MyPlugin\Providers;

use WPFoundation\Core\ServiceProvider;
use WPFoundation\Hooks\HookRegistrar;
use MyPlugin\Controllers\AdminController;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 服务由 PHP-DI 自动装配
    }

    public function boot(): void
    {
        $hooks = new HookRegistrar($this->container);

        $hooks->addAction('admin_menu', AdminController::class, 'registerMenu')
              ->registerAll();
    }
}
```

## 🌐 REST API 路由

### 定义路由

在 `routes/api.php` 中：

```php
<?php

use MyPlugin\Controllers\PostController;
use MyPlugin\Controllers\UserController;

// 公开路由
$router->get('/posts', PostController::class, 'index');
$router->get('/posts/(?P<id>\d+)', PostController::class, 'show');

// 需要认证的路由
$router->middleware('auth')->group([], function ($router) {
    $router->post('/posts', PostController::class, 'store');
    $router->put('/posts/(?P<id>\d+)', PostController::class, 'update');
    $router->delete('/posts/(?P<id>\d+)', PostController::class, 'destroy');
});

// 需要管理员权限
$router->middleware('admin')->get('/users', UserController::class, 'index');
```

### Controller 示例

```php
<?php

namespace MyPlugin\Controllers;

use WPFoundation\Http\Request;
use WPFoundation\Http\Response;
use WPFoundation\Exceptions\NotFoundException;
use WP_REST_Response;

class PostController
{
    public function index(Request $request): WP_REST_Response
    {
        $posts = get_posts();
        return Response::success($posts, '获取成功');
    }

    public function show(Request $request): WP_REST_Response
    {
        $post = get_post($request->route('id'));
        
        if (!$post) {
            throw NotFoundException::make('文章');
        }
        
        return Response::success($post, '获取成功');
    }

    public function store(Request $request): WP_REST_Response
    {
        $postId = wp_insert_post([
            'post_title' => $request->input('title'),
            'post_content' => $request->input('content'),
        ]);

        return Response::success(['id' => $postId], '创建成功', 201);
    }
}
```

## 📝 Request 对象

```php
// 获取参数
$name = $request->get('name');
$email = $request->input('email');
$id = $request->route('id');
$page = $request->query('page', 1);

// 获取用户
$user = $request->user();
$isAuth = $request->isAuthenticated();

// 验证参数
$errors = $request->validate([
    'email' => 'required',
    'name' => 'required',
]);

// 只获取指定参数
$data = $request->only(['name', 'email']);
$data = $request->except(['password']);
```

## 📤 Response 对象

### 统一响应格式

```json
{
  "code": 0,
  "data": { ... },
  "msg": "success"
}
```

### 使用方法

```php
// 成功响应
return Response::success($data, '操作成功');

// 错误响应
return Response::error('操作失败', 1001);

// 快捷方法
return Response::notFound('资源不存在');
return Response::unauthorized('用户未登录');
return Response::forbidden('权限不足');
return Response::validationError($errors, '验证失败');

// 分页响应
return Response::paginate($items, $total, $page, $perPage, '获取成功');
```

## ⚠️ 异常处理

### 抛出异常

```php
use WPFoundation\Exceptions\NotFoundException;
use WPFoundation\Exceptions\ValidationException;
use WPFoundation\Exceptions\UnauthorizedException;

// 资源未找到
if (!$post) {
    throw NotFoundException::make('文章');
}

// 验证失败
if (!empty($errors)) {
    throw ValidationException::withErrors($errors);
}

// 未授权
if (!$request->isAuthenticated()) {
    throw UnauthorizedException::make('请先登录');
}
```

### 自动处理

所有异常会被自动捕获、记录日志并返回标准响应：

```json
{
  "code": 404,
  "data": null,
  "msg": "文章不存在"
}
```

## 🔧 WordPress 服务

### PostTypeService

```php
use WPFoundation\WordPress\Services\PostTypeService;

$service = $app->make(PostTypeService::class);

$service->register('portfolio', [
    'public' => true,
    'has_archive' => true,
], [
    'name' => 'Portfolios',
    'singular_name' => 'Portfolio',
])->registerAll();
```

### MenuService

```php
use WPFoundation\WordPress\Services\MenuService;

$service = $app->make(MenuService::class);

$service->registerLocations([
    'primary' => 'Primary Menu',
    'footer' => 'Footer Menu',
])->register();
```

### TaxonomyService

```php
use WPFoundation\WordPress\Services\TaxonomyService;

$service = $app->make(TaxonomyService::class);

$service->register('category', 'portfolio', [
    'hierarchical' => true,
])->registerAll();
```

### ShortcodeService

```php
use WPFoundation\WordPress\Services\ShortcodeService;

$service = $app->make(ShortcodeService::class);

$service->register('button', function ($atts) {
    return '<button>' . $atts['text'] . '</button>';
})->registerAll();
```

## 🏗️ 架构

```
Application (应用层)
    ↓
WP Foundation Framework
    ├── Core (Container, ServiceProvider, Application)
    ├── Http (Router, Request, Response)
    ├── Exceptions (ExceptionHandler, 异常类)
    ├── Hooks (HookRegistrar)
    ├── Services (OptionService)
    └── WordPress (PostType, Menu, Taxonomy, Shortcode, Customize)
    ↓
Third-party Libraries (PHP-DI, PSR)
```

## 📋 组件列表

### 核心组件
- `Application` - 应用引导
- `Container` - DI 容器（PHP-DI）
- `ServiceProvider` - 服务提供者基类

### HTTP 组件
- `Router` - 路由注册器
- `Request` - 请求对象
- `Response` - 响应对象
- `ResponseCode` - 响应码常量

### 异常组件
- `ExceptionHandler` - 异常处理器
- `ApiException` - API 异常
- `NotFoundException` - 404 异常
- `ValidationException` - 验证异常
- `UnauthorizedException` - 401 异常
- `ForbiddenException` - 403 异常

### 钩子组件
- `HookRegistrar` - 钩子注册器

### 服务组件
- `OptionService` - 选项服务

### WordPress 服务
- `PostTypeService` - 文章类型
- `TaxonomyService` - 分类法
- `MenuService` - 菜单
- `ShortcodeService` - 短代码
- `CustomizeService` - 主题自定义

## 📚 完整示例

查看 `USAGE-EXAMPLE.md` 获取完整的使用示例。

## 🔧 系统要求

- PHP >= 7.4
- WordPress >= 5.6
- Composer

## 📄 许可证

MIT License

## 👤 作者

Hollisho

## 🔗 相关资源

- [PHP-DI 文档](https://php-di.org/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PSR 标准](https://www.php-fig.org/psr/)
