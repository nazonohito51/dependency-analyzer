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
        return $this->getFullyQualifiedClassName() === $that->getFullyQualifiedClassName();
    }

    public function isTrait(): bool
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
