<?php
declare(strict_types = 1);
namespace HPlus\Route\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Header extends Param
{
    public $in = 'header';
}
