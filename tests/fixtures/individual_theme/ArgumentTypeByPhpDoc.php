<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

class ArgumentTypeByPhpDoc
{
    /**
     * PhpDoc constructor.
     * @param \Tests\Fixtures\IndividualTheme\Foundations\SomeClass1|\Tests\Fixtures\IndividualTheme\Foundations\SomeClass2 $someClass
     */
    public function __construct($someClass)
    {
    }
}
