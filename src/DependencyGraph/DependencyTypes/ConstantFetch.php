<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class ConstantFetch extends Base
{
    /**
     * @var string
     */
    private $constantName;
    /**
     * @var string
     */
    private $caller;

    public function __construct(string $constantName, string $caller = null)
    {
        $this->constantName = $constantName;
        $this->caller = $caller;
    }

    public function getConstantName(): string
    {
        return $this->constantName;
    }

    public function getCaller(): ?string
    {
        return $this->caller;
    }

    public function getType(): string
    {
        return DependencyGraph::TYPE_CONSTANT_FETCH;
    }

    public function isEqual(Base $that): bool
    {
        return (
            $that instanceof self &&
            $this->getConstantName() === $that->getConstantName() &&
            $this->getCaller() === $that->getCaller()
        );
    }
}
