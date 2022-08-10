<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ApiController extends Controller
{

}
