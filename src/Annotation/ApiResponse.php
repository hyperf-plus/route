<?php
declare(strict_types=1);

namespace HPlus\Route\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiResponse extends AbstractAnnotation
{

    public function __construct(
        public int     $code = 200,
        public int     $response = 200,  // 兼容性别名
        public array   $schema = [],
        public ?string $description = null,
    )
    {
        // 如果使用了response参数，将其值赋给code
        if ($response !== 200 && $code === 200) {
            $this->code = $response;
        }
    }

}
