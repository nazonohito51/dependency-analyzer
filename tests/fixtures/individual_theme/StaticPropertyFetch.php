<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass3;

class StaticPropertyFetch
{
    public function someMethod()
    {
        SomeClass3::$someStatic;
    }
}
