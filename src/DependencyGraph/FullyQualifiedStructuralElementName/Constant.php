<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Constant extends Base
{
    function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_CONSTANT;
    }

    public function include(Base $that): bool
    {
        return false;
    }

    public function isConstant(): bool
    {
        return true;
    }
}
