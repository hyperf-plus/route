<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller]
class IndexController
{
    #[GetMapping(path: "/")]
    public function index()
    {
        return [
            'message' => 'Welcome to Hyperf!',
            'version' => '3.1',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    #[GetMapping(path: "/health")]
    public function health()
    {
        return [
            'status' => 'ok',
            'timestamp' => time()
        ];
    }
}