<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass2;

class PropertyFetch
{
    public function someMethod()
    {
        $someClass2 = new SomeClass2();
        $someClass2->someProperty;
    }
}
