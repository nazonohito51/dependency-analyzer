<?php
declare(strict_types=1);

namespace Tests\Fixtures;

class ArgumentTypeByPhpDoc
{
    /**
     * PhpDoc constructor.
     * @param \Tests\Fixtures\Foundations\SomeClass1|\Tests\Fixtures\Foundations\SomeClass2 $someClass
     */
    public function __construct($someClass)
    {
    }
}
