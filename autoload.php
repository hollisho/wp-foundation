<?php

/**
 * WP Foundation 自动加载文件
 * 用于手动加载框架（不通过 Composer）
 */

// PSR-4 自动加载器
spl_autoload_register(function ($class) {
    $prefix = 'WPFoundation\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 加载辅助函数
require_once __DIR__ . '/src/Support/Helpers/helpers.php';
