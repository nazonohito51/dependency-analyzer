<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Class_ extends Base
{
    function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_CLASS;
    }

    public function include(Base $that): bool
    {
        // TODO: Implement include() method.
    }

    public function isClass(): bool
    {
        return true;
    }
}
