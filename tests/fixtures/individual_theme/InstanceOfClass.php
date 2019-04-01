<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass1;

class InstanceOfClass
{
    public function someMethod($someClass)
    {
        if ($someClass instanceof SomeClass1) {
        }
    }
}
