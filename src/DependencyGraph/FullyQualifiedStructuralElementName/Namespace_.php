<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Namespace_ extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_NAMESPACE;
    }

    public function include(Base $that): bool
    {
        if ($this->toString() === '\\') {
            // Pattern likely '\\' will match with all className.
            return true;
        } elseif ($this->isSame($that)) {
            return true;
        } elseif (count($this->getFullyQualifiedNamespaceName()) > count($this->getFullyQualifiedNamespaceName())) {
            return false;
        }

        $namesOfThat = $that->getFullyQualifiedNamespaceName();
        foreach ($this->getFullyQualifiedNamespaceName() as $index => $name) {
            if (!isset($namesOfThat[$index]) || $namesOfThat[$index] !== $name) {
                return false;
            }
        }

        return true;
    }

    public function isNamespace(): bool
    {
        return true;
    }

    public function getFullyQualifiedNamespaceName(): array
    {
        if ($this->toString() === '\\') {
            return [];
        }

        return explode('\\', trim($this->toString(), '\\'));
    }

    public function getFullyQualifiedClassName(): ?array
    {
        return null;
    }
}
