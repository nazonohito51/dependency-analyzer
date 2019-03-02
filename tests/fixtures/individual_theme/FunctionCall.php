<?php
declare(strict_types=1);

namespace Tests\Fixtures;

class FunctionCall
{
    public function __construct()
    {
        $someClass1 = some_function1();
    }
}
