<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Trait_ extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_TRAIT;
    }

    public function include(Base $that): bool
    {
        // TODO: Implement include() method.
    }

    public function isTrait(): bool
    {
        return true;
    }
}
