<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass1;

class MethodCall
{
    public function someMethod()
    {
        $someClass1 = new SomeClass1();
        $someClass1->someMethod();
    }
}
