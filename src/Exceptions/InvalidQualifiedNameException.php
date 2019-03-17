<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class InvalidQualifiedNameException extends RuntimeException
{
    /**
     * @var string
     */
    private $qualifiedName;

    public function __construct(string $qualifiedName)
    {
        parent::__construct('Invalid qualified name: ' . $qualifiedName);
        $this->qualifiedName = $qualifiedName;
    }
}
