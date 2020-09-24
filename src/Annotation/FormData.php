<?php
declare(strict_types = 1);
namespace HPlus\Route\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class FormData extends Param
{
    public $in = 'formData';
    public $scene = '';
}
