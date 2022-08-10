<?php

namespace HPlus\Route\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;

abstract class Mapping extends AbstractAnnotation
{
    /**
     * @param string|null $path 路径
     * @param string|null $summary 说明
     * @param string|null $description 介绍
     * @param string|null $deprecated
     * @param bool $security 是否验证用户权限
     * @param bool $userOpen 是否对登录用户开放
     * @param array $methods
     * @param array $options
     */

    public function __construct(public ?string $path = null,
                                public ?string $summary = null,
                                public ?string $description = null,
                                public ?string $deprecated = null,
                                public bool    $security = true,
                                public bool    $userOpen = true,
                                public array   $methods = [],
                                public array   $options = [])
    {
        foreach (array_filter(array_merge(func_get_args() + get_class_vars(static::class))) as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }
}
