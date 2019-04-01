<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

use Tests\Fixtures\IndividualTheme\Foundations\SomeException;

class CatchException
{
    public function someMethod()
    {
        try {
        } catch (SomeException $someException) {
        }
    }
}
