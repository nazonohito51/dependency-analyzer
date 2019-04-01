<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeClass3;

class StaticCall
{
    public function someMethod()
    {
        SomeClass3::someStaticMethod();
    }
}
