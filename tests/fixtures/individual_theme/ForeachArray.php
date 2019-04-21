<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass2;

class ForeachArray
{
    public function testMethod()
    {
        $ret = [];

        foreach ([new SomeClass2, new SomeClass2, new SomeClass2] as $someClass2) {
            $ret = $someClass2->someProperty;
        };

        return $ret;
    }
}
