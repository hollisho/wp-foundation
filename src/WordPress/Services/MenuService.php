<?php

namespace WPFoundation\WordPress\Services;

/**
 * 菜单注册服务
 * 封装 WordPress Menu API
 */
class MenuService
{
    protected array $locations = [];

    /**
     * 注册菜单位置
     */
    public function registerLocation(string $location, string $description): self
    {
        $this->locations[$location] = $description;
        return $this;
    }

    /**
     * 批量注册菜单位置
     */
    public function registerLocations(array $locations): self
    {
        $this->locations = array_merge($this->locations, $locations);
        return $this;
    }

    /**
     * 执行注册
     */
    public function register(): void
    {
        if (!empty($this->locations)) {
            register_nav_menus($this->locations);
        }
    }

    /**
     * 获取菜单
     */
    public function getMenu(string $location, array $args = []): string
    {
        $defaults = [
            'theme_location' => $location,
            'echo' => false,
        ];

        return wp_nav_menu(array_merge($defaults, $args));
    }

    /**
     * 检查位置是否有菜单
     */
    public function hasMenu(string $location): bool
    {
        return has_nav_menu($location);
    }
}
