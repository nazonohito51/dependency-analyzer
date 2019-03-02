<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;

class NewClass
{
    public function someMethod()
    {
        new SomeClass1();
    }
}
