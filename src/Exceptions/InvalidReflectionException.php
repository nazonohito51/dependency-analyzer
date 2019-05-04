<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class InvalidReflectionException extends Base
{
    private $reflection;

    public function __construct($reflection, string $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->reflection = $reflection;
    }

    public function getInvalidReflection()
    {
        return $this->reflection;
    }
}
