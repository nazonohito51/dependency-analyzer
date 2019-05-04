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
        return $this->getFullyQualifiedClassNameAsArray() === $that->getFullyQualifiedClassNameAsArray();
    }

    public function isClass(): bool
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

    public function createMethodFQSEN(string $methodName): Method
    {
        return FullyQualifiedStructuralElementName::createMethod($this->toString(), $methodName);
    }

    public function createPropertyFQSEN(string $propertyName): Property
    {
        return FullyQualifiedStructuralElementName::createProperty($this->toString(), $propertyName);
    }

    public function createClassConstantFQSEN(string $constantName): ClassConstant
    {
        return FullyQualifiedStructuralElementName::createClassConstant($this->toString(), $constantName);
    }
}
