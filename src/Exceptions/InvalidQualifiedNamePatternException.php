<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class InvalidQualifiedNamePatternException extends RuntimeException
{
    /**
     * @var string
     */
    private $pattern;

    public function __construct(string $pattern)
    {
        parent::__construct('Invalid pattern: ' . $pattern);
        $this->pattern = $pattern;
    }
}
