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
        return $this->getFullyQualifiedClassName() === $that->getFullyQualifiedClassName();
    }

    public function isClass(): bool
    {
        return true;
    }

    public function getFullyQualifiedNamespaceName(): array
    {
        $names = $this->getFullyQualifiedClassName();
        array_pop($names);

        return $names;
    }

    public function getFullyQualifiedClassName(): ?array
    {
        return explode('\\', substr($this->toString(), 1));
    }
}
