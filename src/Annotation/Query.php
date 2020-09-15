<?php
declare(strict_types = 1);
namespace HPlus\Route\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Query extends Param
{
    public $in = 'query';
    public $scene = '';
    public $validate = '';
}
