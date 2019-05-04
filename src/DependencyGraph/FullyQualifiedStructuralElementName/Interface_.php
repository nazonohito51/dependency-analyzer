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
        return $this->getFullyQualifiedClassNameAsArray() === $that->getFullyQualifiedClassNameAsArray();
    }

    public function isInterface(): bool
    {
        return true;
    }

    public function getFullyQualifiedNamespaceNameAsArray(): array
    {
        $names = $this->getFullyQualifiedClassNameAsArray();
        array_pop($names);

        return $names;
    }

    public function getFullyQualifiedClassNameAsArray(): ?array
    {
        return explode('\\', substr($this->toString(), 1));
    }
}
