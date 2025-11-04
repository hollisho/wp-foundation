<?php

namespace WPFoundation\WordPress\Services;

use WP_Customize_Manager;

/**
 * 主题自定义服务
 * 封装 WordPress Customizer API
 */
class CustomizeService
{
    protected array $panels = [];
    protected array $sections = [];
    protected array $settings = [];
    protected array $controls = [];

    /**
     * 添加面板
     */
    public function addPanel(string $id, array $args): self
    {
        $this->panels[$id] = $args;
        return $this;
    }

    /**
     * 批量添加面板
     */
    public function addPanels(array $panels): self
    {
        foreach ($panels as $id => $args) {
            $this->addPanel($id, $args);
        }
        return $this;
    }

    /**
     * 添加区块
     */
    public function addSection(string $id, array $args): self
    {
        $this->sections[$id] = $args;
        return $this;
    }

    /**
     * 批量添加区块
     */
    public function addSections(array $sections): self
    {
        foreach ($sections as $id => $args) {
            $this->addSection($id, $args);
        }
        return $this;
    }

    /**
     * 添加设置
     */
    public function addSetting(string $id, array $args = []): self
    {
        $this->settings[$id] = array_merge([
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh',
        ], $args);
        return $this;
    }

    /**
     * 批量添加设置
     */
    public function addSettings(array $settings): self
    {
        foreach ($settings as $id => $args) {
            $this->addSetting($id, $args);
        }
        return $this;
    }

    /**
     * 添加控件
     */
    public function addControl(string $id, array $args): self
    {
        $this->controls[$id] = $args;
        return $this;
    }

    /**
     * 批量添加控件
     */
    public function addControls(array $controls): self
    {
        foreach ($controls as $id => $args) {
            $this->addControl($id, $args);
        }
        return $this;
    }

    /**
     * 注册到 WordPress Customizer
     */
    public function register(WP_Customize_Manager $wp_customize): void
    {
        // 注册面板
        foreach ($this->panels as $id => $args) {
            $wp_customize->add_panel($id, $args);
        }

        // 注册区块
        foreach ($this->sections as $id => $args) {
            $wp_customize->add_section($id, $args);
        }

        // 注册设置
        foreach ($this->settings as $id => $args) {
            $wp_customize->add_setting($id, $args);
        }

        // 注册控件
        foreach ($this->controls as $id => $args) {
            $wp_customize->add_control($id, $args);
        }
    }

    /**
     * 获取主题设置值
     */
    public function get(string $key, $default = null)
    {
        return get_theme_mod($key, $default);
    }

    /**
     * 设置主题设置值
     */
    public function set(string $key, $value): void
    {
        set_theme_mod($key, $value);
    }

    /**
     * 移除主题设置
     */
    public function remove(string $key): void
    {
        remove_theme_mod($key);
    }
}
