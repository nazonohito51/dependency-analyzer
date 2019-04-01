<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

class NoDependency
{
    const CONSTANT = 1;

    static $static;

    private $property1;

    /**
     * @var bool
     */
    private $property2;

    public function method($argument)
    {
    }

    /**
     * @param string $argument
     * @return bool
     */
    public function methodHavePhpDoc($argument)
    {
    }

    public function methodHaveTypeHint(int $argument)
    {
    }

    public function methodHaveReturnType(): string
    {
    }
}
