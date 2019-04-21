<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class PropertyFetch extends Base
{
    /**
     * @var string
     */
    private $propertyName;
    /**
     * @var string
     */
    private $caller;

    public function __construct(string $propertyName, string $caller = null)
    {
        $this->propertyName = $propertyName;
        $this->caller = $caller;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getCaller(): ?string
    {
        return $this->caller;
    }

    public function getType(): string
    {
        return DependencyGraph::TYPE_PROPERTY_FETCH;
    }

    public function isEqual(Base $that): bool
    {
        return (
            $that instanceof self &&
            $this->getPropertyName() === $that->getPropertyName() &&
            $this->getCaller() === $that->getCaller()
        );
    }

    public function toString(): string
    {
        $ret = "{$this->getType()}:{$this->getPropertyName()}";
        if ($this->getCaller()) {
            $ret .= ":{$this->getCaller()}";
        }

        return $ret;
    }
}
