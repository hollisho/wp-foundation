<?php

namespace WPFoundation\Core;

use Closure;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * 容器适配器
 * 封装 PHP-DI，提供兼容的接口
 */
class Container
{
    protected ContainerInterface $container;
    protected array $definitions = [];

    public function __construct()
    {
        $builder = new ContainerBuilder();

        // 生产环境启用编译缓存
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            $cacheDir = $this->getCacheDirectory();
            $builder->enableCompilation($cacheDir);
        }

        // 启用自动装配
        $builder->useAutowiring(true);

        $this->container = $builder->build();
    }

    /**
     * 绑定服务到容器（非单例）
     */
    public function bind(string $abstract, Closure $concrete): void
    {
        $this->definitions[$abstract] = \DI\factory($concrete);
        $this->rebuildContainer();
    }

    /**
     * 绑定单例服务到容器
     */
    public function singleton(string $abstract, Closure $concrete): void
    {
        $this->definitions[$abstract] = \DI\factory($concrete);
        $this->rebuildContainer();
    }

    /**
     * 从容器中解析服务
     */
    public function make(string $abstract)
    {
        return $this->container->get($abstract);
    }

    /**
     * 检查服务是否已绑定
     */
    public function has(string $abstract): bool
    {
        return $this->container->has($abstract);
    }

    /**
     * 获取底层 PHP-DI 容器实例
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 添加定义文件
     */
    public function addDefinitions($definitions): void
    {
        if (is_string($definitions)) {
            $definitions = require $definitions;
        }

        $this->definitions = array_merge($this->definitions, $definitions);
        $this->rebuildContainer();
    }

    /**
     * 重建容器（当添加新定义时）
     */
    protected function rebuildContainer(): void
    {
        $builder = new ContainerBuilder();

        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            $cacheDir = $this->getCacheDirectory();
            $builder->enableCompilation($cacheDir);
        }

        $builder->useAutowiring(true);
        $builder->addDefinitions($this->definitions);

        $this->container = $builder->build();
    }

    /**
     * 获取缓存目录
     */
    protected function getCacheDirectory(): string
    {
        $uploadDir = wp_upload_dir();
        $cacheDir = $uploadDir['basedir'] . '/wp-foundation-cache/di';

        if (!file_exists($cacheDir)) {
            wp_mkdir_p($cacheDir);
        }

        return $cacheDir;
    }
}
