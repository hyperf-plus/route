<?php

declare(strict_types=1);

namespace HPlus\Route\Tests\Unit\Helper;

use HPlus\Route\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

/**
 * StringHelper 单元测试
 */
final class StringHelperTest extends TestCase
{
    // ========== camelToKebab 测试 ==========

    /**
     * @test
     */
    public function it_converts_camel_case_to_kebab_case(): void
    {
        $this->assertEquals('user-profile', StringHelper::camelToKebab('userProfile'));
        $this->assertEquals('get-user-info', StringHelper::camelToKebab('getUserInfo'));
        $this->assertEquals('api-v2-users', StringHelper::camelToKebab('apiV2Users'));
        $this->assertEquals('abc', StringHelper::camelToKebab('ABC'));
        $this->assertEquals('hello', StringHelper::camelToKebab('hello'));
        $this->assertEquals('hello-world-test', StringHelper::camelToKebab('helloWorldTest'));
    }

    /**
     * @test
     */
    public function it_handles_single_word(): void
    {
        $this->assertEquals('users', StringHelper::camelToKebab('users'));
        $this->assertEquals('api', StringHelper::camelToKebab('api'));
    }

    /**
     * @test
     */
    public function it_handles_already_kebab_case(): void
    {
        $this->assertEquals('user-profile', StringHelper::camelToKebab('user-profile'));
    }

    /**
     * @test
     */
    public function it_handles_consecutive_uppercase(): void
    {
        $this->assertEquals('api-users', StringHelper::camelToKebab('APIUsers'));
        $this->assertEquals('html-parser', StringHelper::camelToKebab('HTMLParser'));
    }

    // ========== pluralize 测试 ==========

    /**
     * @test
     */
    public function it_pluralizes_regular_words(): void
    {
        $this->assertEquals('users', StringHelper::pluralize('user'));
        $this->assertEquals('posts', StringHelper::pluralize('post'));
        $this->assertEquals('comments', StringHelper::pluralize('comment'));
    }

    /**
     * @test
     */
    public function it_pluralizes_words_ending_in_y(): void
    {
        $this->assertEquals('categories', StringHelper::pluralize('category'));
        $this->assertEquals('cities', StringHelper::pluralize('city'));
        // 'key' -> 'keys' (vowel before y)
        $this->assertEquals('keys', StringHelper::pluralize('key'));
    }

    /**
     * @test
     */
    public function it_pluralizes_words_ending_in_s_x_ch_sh(): void
    {
        $this->assertEquals('boxes', StringHelper::pluralize('box'));
        $this->assertEquals('watches', StringHelper::pluralize('watch'));
        $this->assertEquals('dishes', StringHelper::pluralize('dish'));
    }

    /**
     * @test
     */
    public function it_handles_irregular_plurals(): void
    {
        $this->assertEquals('people', StringHelper::pluralize('person'));
        $this->assertEquals('children', StringHelper::pluralize('child'));
        $this->assertEquals('men', StringHelper::pluralize('man'));
        $this->assertEquals('women', StringHelper::pluralize('woman'));
        $this->assertEquals('teeth', StringHelper::pluralize('tooth'));
        $this->assertEquals('feet', StringHelper::pluralize('foot'));
        $this->assertEquals('mice', StringHelper::pluralize('mouse'));
        $this->assertEquals('geese', StringHelper::pluralize('goose'));
    }

    /**
     * @test
     */
    public function it_keeps_already_plural_words(): void
    {
        $this->assertEquals('users', StringHelper::pluralize('users'));
        $this->assertEquals('categories', StringHelper::pluralize('categories'));
    }
}
