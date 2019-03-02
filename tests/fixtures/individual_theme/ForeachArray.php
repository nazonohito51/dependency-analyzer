<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass2;

class ForeachArray
{
    /**
     * @param SomeClass2[] $array
     */
    public function someMethod(array $array)
    {
        foreach ($array as $item) {
            $item->someProperty;
        }
    }
}
