<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

abstract class Base
{
    abstract public function getType(): string;
    abstract public function include(Base $that): bool;
    abstract public function getFullyQualifiedNamespaceNameAsArray(): array;
    abstract public function getFullyQualifiedClassNameAsArray(): ?array;

    /**
     * @var string
     */
    protected $elementName;

    /**
     * @param string $elementName
     * @deps-internal \DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName
     * @deps-internal \DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\
     */
    public function __construct(string $elementName)
    {
        $this->elementName = $elementName;
    }

    public function toString(): string
    {
        return $this->elementName;
    }

    public function isNamespace(): bool
    {
        return false;
    }

    public function isClass(): bool
    {
        return false;
    }

    public function isMethod(): bool
    {
        return false;
    }

    public function isProperty(): bool
    {
        return false;
    }

    public function isClassConstant(): bool
    {
        return false;
    }

    public function isInterface(): bool
    {
        return false;
    }

    public function isTrait(): bool
    {
        return false;
    }

    public function isFunction(): bool
    {
        return false;
    }

    public function isConstant(): bool
    {
        return false;
    }

    public function isSame(Base $that): bool
    {
        return $this->getType() === $that->getType() && $this->toString() === $that->toString();
    }

    public function getFullyQualifiedNamespaceName(): Namespace_
    {
        return FullyQualifiedStructuralElementName::createNamespace(implode('\\', $this->getFullyQualifiedNamespaceNameAsArray()));
    }
}
