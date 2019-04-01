<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

class FunctionCall
{
    public function __construct()
    {
        $someClass1 = Foundations\some_function1();
    }
}
