<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Property extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_PROPERTY;
    }

    public function include(Base $that): bool
    {
        return $this->isSame($that);
    }

    public function isProperty(): bool
    {
        return true;
    }
}
