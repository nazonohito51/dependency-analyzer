<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass1;
use Tests\Fixtures\IndividualTheme\Foundations\SomeClass2;
use Tests\Fixtures\IndividualTheme\Foundations\SomeClass3;

class Closure
{
    public function someMethod()
    {
        return function (SomeClass1 $someClass1): SomeClass2 {
            return new SomeClass3();
        };
    }
}
