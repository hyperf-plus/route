<?php
declare(strict_types=1);

namespace HPlus\Route;

use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\ReflectionManager;

class ApiAnnotation
{
    public static function methodMetadata($className, $methodName)
    {
        $reflectMethod = ReflectionManager::reflectMethod($className, $methodName);
        $reader = new AnnotationReader();
        return $reader->getMethodAnnotations($reflectMethod);
    }
}