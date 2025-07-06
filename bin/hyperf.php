#!/usr/bin/env php
<?php

declare(strict_types=1);

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

$application = new Symfony\Component\Console\Application();

if (isset($GLOBALS['argv'][1]) && $GLOBALS['argv'][1] === 'start') {
    $application->add(new Hyperf\Server\Command\StartServer());
    $application->run(new Symfony\Component\Console\Input\ArgvInput(['', 'start']));
} else {
    $application->add(new Hyperf\Server\Command\StartServer());
    $application->run();
}