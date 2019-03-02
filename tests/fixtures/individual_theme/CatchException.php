<?php
declare(strict_types=1);

namespace Tests\Fixtures;

use Tests\Fixtures\Foundations\SomeException;

class CatchException
{
    public function someMethod()
    {
        try {
        } catch (SomeException $someException) {
        }
    }
}
