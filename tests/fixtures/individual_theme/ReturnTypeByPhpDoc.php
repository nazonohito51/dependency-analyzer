<?php
declare(strict_types=1);

namespace Tests\Fixtures;

class ReturnTypeByPhpDoc
{
    /**
     * @return \Tests\Fixtures\Foundations\SomeClass1
     */
    public function someMethod()
    {
    }
}
