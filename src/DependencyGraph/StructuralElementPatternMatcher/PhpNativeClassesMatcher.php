<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher\Matchable;

class PhpNativeClassesMatcher implements Matchable
{
    /**
     * @var string[]
     */
    private $phpNativeClassNames;

    public function __construct(array $phpNativeClassNames)
    {
        $this->phpNativeClassNames = $phpNativeClassNames;
    }

    public function isMatch(FQSEN $target): bool
    {
        return in_array($target->toString(), $this->phpNativeClassNames);
    }

    public function getPattern(): string
    {
        return StructuralElementPatternMatcher::PHP_NATIVE_CLASSES;
    }
}
