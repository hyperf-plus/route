<?php
namespace HPlus\Route\Annotation;

use Hyperf\HttpServer\Annotation\Mapping;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class GetApi extends Mapping
{

    public $path;
    public $summary;
    public $description;
    public $deprecated;
    public $methods = ['GET'];
    /**
     * 是否验证用户权限
     * @var bool
     */
    public $security = true;
    /**
     * 是否对登录用户开放
     * @var bool
     */
    public $userOpen = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $val;
                }
            }
        }
    }
}
