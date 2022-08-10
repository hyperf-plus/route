<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;


use Attribute;

/**
 * 后台插件控制器
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AdminController extends Controller
{

}
