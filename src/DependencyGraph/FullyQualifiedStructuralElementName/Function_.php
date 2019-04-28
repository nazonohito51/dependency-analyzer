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
        return $this->isSame($that);
    }

    public function isFunction(): bool
    {
        return true;
    }

    public function getFullyQualifiedNamespaceName(): array
    {
        $names = explode('\\', substr($this->toString(), 1));
        array_pop($names);

        return $names;
    }

    public function getFullyQualifiedClassName(): ?array
    {
        return null;
    }
}
