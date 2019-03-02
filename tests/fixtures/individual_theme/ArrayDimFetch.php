<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass3;

class ArrayDimFetch
{
    /**
     * @param SomeClass3[] $array
     */
    public function someMethod(array $array)
    {
        $array[0];
    }
}
