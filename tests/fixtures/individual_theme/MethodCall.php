<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;

class MethodCall
{
    public function someMethod()
    {
        $someClass1 = new SomeClass1();
        $someClass1->someMethod();
    }
}
