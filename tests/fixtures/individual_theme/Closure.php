<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;
use Tests\Fixtures\Foundations\SomeClass2;
use Tests\Fixtures\Foundations\SomeClass3;

class Closure
{
    public function someMethod()
    {
        return function (SomeClass1 $someClass1): SomeClass2 {
            return new SomeClass3();
        };
    }
}
