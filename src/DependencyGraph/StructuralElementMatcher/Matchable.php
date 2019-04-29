<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\StructuralElementMatcher;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;

interface Matchable
{
    public function isMatch(FQSEN $target): bool;
    public function getPattern(): string;
}
