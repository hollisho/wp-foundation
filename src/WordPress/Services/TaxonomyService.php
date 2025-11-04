<?php

namespace WPFoundation\WordPress\Services;

/**
 * 自定义分类服务
 * 封装 WordPress Taxonomy API
 */
class TaxonomyService
{
    protected array $taxonomies = [];

    /**
     * 注册分类法
     */
    public function register(
        string $taxonomy,
        $objectType,
        array $args = [],
        array $labels = []
    ): self {
        // 默认标签
        $defaultLabels = [
            'name' => $taxonomy,
            'singular_name' => $taxonomy,
            'search_items' => 'Search ' . $taxonomy,
            'all_items' => 'All ' . $taxonomy,
            'edit_item' => 'Edit ' . $taxonomy,
            'update_item' => 'Update ' . $taxonomy,
            'add_new_item' => 'Add New ' . $taxonomy,
            'new_item_name' => 'New ' . $taxonomy . ' Name',
        ];

        // 默认参数
        $defaultArgs = [
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'show_in_rest' => true,
        ];

        $this->taxonomies[$taxonomy] = [
            'object_type' => (array) $objectType,
            'args' => array_merge($defaultArgs, $args),
            'labels' => array_merge($defaultLabels, $labels),
        ];

        return $this;
    }

    /**
     * 批量注册
     */
    public function registerMultiple(array $taxonomies): self
    {
        foreach ($taxonomies as $taxonomy => $config) {
            $this->register(
                $taxonomy,
                $config['object_type'] ?? 'post',
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
        foreach ($this->taxonomies as $taxonomy => $config) {
            $args = $config['args'];
            $args['labels'] = $config['labels'];

            register_taxonomy($taxonomy, $config['object_type'], $args);
        }
    }

    /**
     * 检查分类法是否存在
     */
    public function exists(string $taxonomy): bool
    {
        return taxonomy_exists($taxonomy);
    }

    /**
     * 获取配置
     */
    public function get(string $taxonomy): ?array
    {
        return $this->taxonomies[$taxonomy] ?? null;
    }

    /**
     * 检查是否已配置
     */
    public function has(string $taxonomy): bool
    {
        return isset($this->taxonomies[$taxonomy]);
    }
}
