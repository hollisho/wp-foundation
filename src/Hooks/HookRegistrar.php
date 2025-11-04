<?php

namespace WPFoundation\Hooks;

use WPFoundation\Core\Container;

/**
 * 钩子注册器
 * 提供优雅的方式注册 WordPress 钩子
 */
class HookRegistrar
{
    protected Container $container;
    protected array $hooks = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 添加 Action 钩子
     */
    public function addAction(
        string $hook,
        string $class,
        string $method,
        int $priority = 10,
        int $acceptedArgs = 1
    ): self {
        $this->hooks[] = [
            'type' => 'action',
            'hook' => $hook,
            'class' => $class,
            'method' => $method,
            'priority' => $priority,
            'acceptedArgs' => $acceptedArgs,
        ];

        return $this;
    }

    /**
     * 添加 Filter 钩子
     */
    public function addFilter(
        string $hook,
        string $class,
        string $method,
        int $priority = 10,
        int $acceptedArgs = 1
    ): self {
        $this->hooks[] = [
            'type' => 'filter',
            'hook' => $hook,
            'class' => $class,
            'method' => $method,
            'priority' => $priority,
            'acceptedArgs' => $acceptedArgs,
        ];

        return $this;
    }

    /**
     * 添加原始 Action（支持闭包和复杂逻辑）
     */
    public function addRawAction(
        string $hook,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): self {
        add_action($hook, $callback, $priority, $acceptedArgs);
        return $this;
    }

    /**
     * 添加原始 Filter（支持闭包和复杂逻辑）
     */
    public function addRawFilter(
        string $hook,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): self {
        add_filter($hook, $callback, $priority, $acceptedArgs);
        return $this;
    }

    /**
     * 注册所有钩子
     */
    public function registerAll(): void
    {
        foreach ($this->hooks as $hookData) {
            $instance = $this->container->make($hookData['class']);

            if ($hookData['type'] === 'filter') {
                add_filter(
                    $hookData['hook'],
                    [$instance, $hookData['method']],
                    $hookData['priority'],
                    $hookData['acceptedArgs']
                );
            } else {
                add_action(
                    $hookData['hook'],
                    [$instance, $hookData['method']],
                    $hookData['priority'],
                    $hookData['acceptedArgs']
                );
            }
        }

        // 清空已注册的钩子
        $this->hooks = [];
    }
}
