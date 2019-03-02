<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass1;

class InstanceOfClass
{
    public function someMethod($someClass)
    {
        if ($someClass instanceof SomeClass1) {
        }
    }
}
