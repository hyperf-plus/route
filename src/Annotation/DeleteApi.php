<?php
namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\HttpServer\Annotation\Mapping;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeleteApi extends Mapping
{

}
