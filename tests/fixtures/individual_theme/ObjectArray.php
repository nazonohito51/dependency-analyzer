<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;

class ObjectArray
{
    /**
     * @param SomeClass1[] $array
     */
    public function someMethod(array $array)
    {
        $array[0]->someMethod();
    }
}
