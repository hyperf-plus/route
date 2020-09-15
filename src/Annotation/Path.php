<?php
declare(strict_types = 1);
namespace HPlus\Route\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Path extends Param
{
    public $in = 'path';
    public $userOpen = false;

}
