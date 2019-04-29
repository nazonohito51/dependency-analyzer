<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\StructuralElementMatcher;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;

class FQSENMatcher implements Matchable
{
    /**
     * @var FQSEN
     */
    private $fqsen;

    public function __construct(FQSEN $fqsen)
    {
        $this->fqsen = $fqsen;
    }

    public function isMatch(FQSEN $target): bool
    {
        return $this->fqsen->include($target);
    }
}
