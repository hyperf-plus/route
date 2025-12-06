<?php

declare(strict_types=1);

namespace HPlus\Route\Helper;

/**
 * 字符串处理工具类
 * 
 * 提供 RESTful API 路由生成所需的字符串转换方法
 */
final class StringHelper
{
    /**
     * 驼峰转中划线（kebab-case，现代 RESTful API 标准）
     * 
     * @example userProfile -> user-profile
     * @example getUserInfo -> get-user-info
     * @example APIUsers -> api-users
     */
    public static function camelToKebab(string $str): string
    {
        // 处理连续大写字母后跟小写字母的情况 (APIUsers -> api-users)
        $str = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1-$2', $str);
        // 处理小写字母后跟大写字母的情况 (userProfile -> user-profile)
        $str = preg_replace('/([a-z\d])([A-Z])/', '$1-$2', $str);
        
        return strtolower($str);
    }

    /**
     * 将单词转换为复数形式
     */
    public static function pluralize(string $word): string
    {
        static $irregular = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];
        
        $lowerWord = strtolower($word);
        
        if (isset($irregular[$lowerWord])) {
            return $irregular[$lowerWord];
        }
        
        // 已经是复数形式
        if (str_ends_with($word, 's') || str_ends_with($word, 'es')) {
            return $word;
        }
        
        // 辅音 + y 结尾
        if (preg_match('/[^aeiou]y$/', $word)) {
            return substr($word, 0, -1) . 'ies';
        }
        
        // s, x, z, ch, sh 结尾
        if (preg_match('/(s|x|z|ch|sh)$/', $word)) {
            return $word . 'es';
        }
        
        return $word . 's';
    }
}
