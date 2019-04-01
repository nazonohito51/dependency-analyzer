<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass2;

class ForeachArray
{
    public function someMethod()
    {
        $ret = [];

        foreach ([new SomeClass2, new SomeClass2, new SomeClass2] as $someClass2) {
            $ret = $someClass2->someProperty;
        };

        return $ret;
    }
}
