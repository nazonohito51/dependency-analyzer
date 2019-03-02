<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass3;

class StaticCall
{
    public function someMethod()
    {
        SomeClass3::someStaticMethod();
    }
}
