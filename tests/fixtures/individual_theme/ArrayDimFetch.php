<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;

class ArrayDimFetch
{
    public function someMethod()
    {
        $array = [new SomeClass1(), new SomeClass1(), new SomeClass1()];
        $array[1]->someMethod();
    }
}
