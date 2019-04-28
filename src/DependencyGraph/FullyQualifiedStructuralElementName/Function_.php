<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Function_ extends Base
{
    function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_FUNCTION;
    }

    public function include(Base $that): bool
    {
        return false;
    }

    public function isFunction(): bool
    {
        return true;
    }
}
