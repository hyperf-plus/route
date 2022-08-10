<?php

namespace HPlus\Route\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

abstract class Param extends AbstractAnnotation
{
    /**
     * @param string $key 字段key,相当于"name[|description]"
     * @param string|null $in 位置
     * @param string|null $name 字段名
     * @param string|null $description 字段描述
     * @param bool $required
     * @param bool $security
     * @param string|null $default
     * @param string|null $type
     * @param bool $userOpen
     * @param array $enum 字段枚举值
     * @param string|null $scene
     * @param string|null $validate
     */
    public function __construct(
        public string  $key,
        public ?string $in = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool    $required = false,
        public bool    $security = false,
        public ?string $default = null,
        public ?string $type = null,
        public bool    $userOpen = false,
        public array   $enum = [],
        public ?string $scene = null,
        public ?string $validate = null
    )
    {
        foreach (array_filter(array_merge(func_get_args() + get_class_vars(static::class))) as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }
}
