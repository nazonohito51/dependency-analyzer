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
        } elseif (count($this->getFullyQualifiedNamespaceNameAsArray()) > count($this->getFullyQualifiedNamespaceNameAsArray())) {
            return false;
        }

        $namesOfThat = $that->getFullyQualifiedNamespaceNameAsArray();
        foreach ($this->getFullyQualifiedNamespaceNameAsArray() as $index => $name) {
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

    public function getFullyQualifiedNamespaceNameAsArray(): array
    {
        if ($this->toString() === '\\') {
            return [];
        }

        return explode('\\', trim($this->toString(), '\\'));
    }

    public function getFullyQualifiedClassNameAsArray(): ?array
    {
        return null;
    }
}
