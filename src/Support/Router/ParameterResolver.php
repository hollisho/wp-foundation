<?php
/**
 * @license MIT
 *
 * Modified by gzbd on 18-November-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace WPFoundation\Support\Router;

use WPFoundation\Core\Container;
use WPFoundation\Http\Request;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * 控制器方法参数解析器
 * 负责解析控制器方法参数，支持依赖注入和类型提示
 */
class ParameterResolver
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 解析方法参数
     *
     * @param ReflectionMethod $method 方法反射对象
     * @param array $urlParams URL 路由参数
     * @param Request $request 请求对象
     * @return array 解析后的参数数组
     * @throws \InvalidArgumentException
     */
    public function resolve(ReflectionMethod $method, array $urlParams, Request $request): array
    {
        $args = [];

        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $name = $parameter->getName();

            // 处理类型提示注入
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();

                // 注入 Request 对象
                if ($className === Request::class || is_subclass_of($className, Request::class)) {
                    $args[] = $request;
                    continue;
                }

                // 从容器解析依赖
                if ($this->container->has($className)) {
                    $args[] = $this->container->make($className);
                    continue;
                }

                // 尝试自动装配
                try {
                    $args[] = $this->container->make($className);
                    continue;
                } catch (\Throwable $e) {
                    // 继续尝试其他方式
                }
            }

            // URL 参数注入（如 {id}）
            if (isset($urlParams[$name])) {
                $args[] = $this->castParameter($urlParams[$name], $type);
                continue;
            }

            // 请求参数注入
            if ($request->has($name)) {
                $args[] = $this->castParameter($request->get($name), $type);
                continue;
            }

            // 使用默认值
            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            // 可选参数
            if ($parameter->allowsNull()) {
                $args[] = null;
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf(
                    '无法解析参数 "%s" 在方法 %s::%s',
                    $name,
                    $method->getDeclaringClass()->getName(),
                    $method->getName()
                )
            );
        }

        return $args;
    }

    /**
     * 类型转换参数值
     */
    private function castParameter($value, ?ReflectionNamedType $type)
    {
        if (!$type || $type->isBuiltin() === false) {
            return $value;
        }

        $typeName = $type->getName();

        switch ($typeName) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
                return (string) $value;
            case 'array':
                return is_array($value) ? $value : [$value];
            default:
                return $value;
        }
    }
}
