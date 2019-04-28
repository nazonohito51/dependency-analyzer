<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Method extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_METHOD;
    }

    public function include(Base $that): bool
    {
        return $this->isSame($that);
    }

    public function isMethod(): bool
    {
        return true;
    }
}
