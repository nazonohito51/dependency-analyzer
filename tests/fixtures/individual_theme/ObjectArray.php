<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass1;

class ObjectArray
{
    /**
     * @param SomeClass1[] $array
     */
    public function testMethod(array $array)
    {
        $array[0]->someMethod();
    }
}
