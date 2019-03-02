<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass2;

class PropertyFetch
{
    public function someMethod()
    {
        $someClass2 = new SomeClass2();
        $someClass2->someProperty;
    }
}
