<?php

namespace WPFoundation\WordPress\Services;

/**
 * 自定义文章类型服务
 * 封装 WordPress Post Type API
 */
class PostTypeService
{
    protected array $postTypes = [];

    /**
     * 注册文章类型
     */
    public function register(string $postType, array $args = [], array $labels = []): self
    {
        // 默认标签
        $defaultLabels = [
            'name' => $postType,
            'singular_name' => $postType,
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $postType,
            'edit_item' => 'Edit ' . $postType,
            'new_item' => 'New ' . $postType,
            'view_item' => 'View ' . $postType,
            'search_items' => 'Search ' . $postType,
            'not_found' => 'No ' . $postType . ' found',
            'not_found_in_trash' => 'No ' . $postType . ' found in Trash',
        ];

        // 默认参数
        $defaultArgs = [
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ];

        $this->postTypes[$postType] = [
            'args' => array_merge($defaultArgs, $args),
            'labels' => array_merge($defaultLabels, $labels),
        ];

        return $this;
    }

    /**
     * 批量注册
     */
    public function registerMultiple(array $postTypes): self
    {
        foreach ($postTypes as $postType => $config) {
            $this->register(
                $postType,
                $config['args'] ?? [],
                $config['labels'] ?? []
            );
        }
        return $this;
    }

    /**
     * 执行注册
     */
    public function registerAll(): void
    {
        foreach ($this->postTypes as $postType => $config) {
            $args = $config['args'];
            $args['labels'] = $config['labels'];

            register_post_type($postType, $args);
        }
    }

    /**
     * 检查文章类型是否存在
     */
    public function exists(string $postType): bool
    {
        return post_type_exists($postType);
    }

    /**
     * 获取配置
     */
    public function get(string $postType): ?array
    {
        return $this->postTypes[$postType] ?? null;
    }

    /**
     * 检查是否已配置
     */
    public function has(string $postType): bool
    {
        return isset($this->postTypes[$postType]);
    }
}
