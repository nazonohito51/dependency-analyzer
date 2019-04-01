<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass1;
use Tests\Fixtures\IndividualTheme\Foundations\SomeClass2;

class ArrayDimFetch
{
    public function someMethod()
    {
        $array = [new SomeClass2, new SomeClass1, new SomeClass2];

        return $array[1]->someMethod()->someProperty;
    }
}
