<?php
declare(strict_types=1);

namespace Tests\Fixtures\Foundations;

class SomeClass3
{
    const SOME_CONST = 'some_const';

    /**
     * @var SomeClass2 $someStatic
     */
    static $someStatic;

    public function someMethod()
    {
    }

    public static function someStaticMethod(): SomeClass1
    {
    }
}
