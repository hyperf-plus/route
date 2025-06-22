<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// 确保错误报告级别
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 设置时区
date_default_timezone_set('UTC');

// 创建基础路径常量
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 1));
}

echo "HPlus Route Test Suite Bootstrap Complete\n"; 