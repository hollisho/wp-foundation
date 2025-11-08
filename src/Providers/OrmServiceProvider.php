<?php

namespace WPFoundation\Providers;

use WPFoundation\Core\ServiceProvider;
use WPOrm\Database\ConnectionManager;

/**
 * ORM 服务提供者
 * 将 wp-orm 集成到 wp-foundation
 */
class OrmServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        // 注册连接管理器
        $this->app->singleton(ConnectionManager::class, function () {
            return $this->createConnectionManager();
        });

        // 注册别名
        $this->app->singleton('db', function () {
            return $this->app->make(ConnectionManager::class);
        });
    }

    /**
     * 启动服务
     */
    public function boot(): void
    {
        // 配置数据库连接
        $this->configureDatabase();

        // 如果是多站点，设置当前站点 ID
        if (is_multisite()) {
            ConnectionManager::setSiteId(get_current_blog_id());

            // 监听站点切换
            add_action('switch_blog', function ($new_site_id) {
                ConnectionManager::setSiteId($new_site_id);
            });
        }
    }

    /**
     * 创建连接管理器
     */
    protected function createConnectionManager(): ConnectionManager
    {
        return new ConnectionManager();
    }

    /**
     * 配置数据库
     */
    protected function configureDatabase(): void
    {
        $config = $this->getDatabaseConfig();
        ConnectionManager::configure($config);
    }

    /**
     * 获取数据库配置
     */
    protected function getDatabaseConfig(): array
    {
        // 尝试从应用配置获取
        $config = $this->config('database');

        if ($config) {
            return $config;
        }

        // 使用默认配置
        return $this->getDefaultConfig();
    }

    /**
     * 获取默认配置
     */
    protected function getDefaultConfig(): array
    {
        global $wpdb;

        $config = [
            'default' => 'mysql',
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'host' => DB_HOST,
                    'database' => DB_NAME,
                    'username' => DB_USER,
                    'password' => DB_PASSWORD,
                    'charset' => DB_CHARSET,
                    'collation' => DB_COLLATE ?: 'utf8mb4_unicode_ci',
                    'prefix' => $wpdb->prefix,
                ],
            ],
        ];

        // 如果定义了读写分离配置
        if (defined('DB_READ_HOST') && DB_READ_HOST) {
            $config['connections']['mysql']['read'] = [
                'host' => DB_READ_HOST,
            ];
        }

        if (defined('DB_WRITE_HOST') && DB_WRITE_HOST) {
            $config['connections']['mysql']['write'] = [
                'host' => DB_WRITE_HOST,
            ];
        }

        return $config;
    }
}
