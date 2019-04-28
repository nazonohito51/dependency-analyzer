<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Interface_ extends Base
{
    function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_INTERFACE;
    }

    public function include(Base $that): bool
    {
        // TODO: Implement include() method.
    }

    public function isInterface(): bool
    {
        return true;
    }
}
