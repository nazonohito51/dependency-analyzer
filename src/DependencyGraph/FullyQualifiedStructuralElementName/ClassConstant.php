<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class ClassConstant extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_CLASS_CONSTANT;
    }

    public function include(Base $that): bool
    {
        return $this->isSame($that);
    }

    public function isClassConstant(): bool
    {
        return true;
    }
}
