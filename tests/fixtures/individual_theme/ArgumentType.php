<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeClass;

class ArgumentType
{
    public function __construct(SomeClass $someClass)
    {
    }
}
