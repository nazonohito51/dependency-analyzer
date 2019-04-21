<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class MethodCall extends Base
{
    /**
     * @var string
     */
    private $callee;
    /**
     * @var string
     */
    private $caller;

    public function __construct(string $callee, string $caller = null)
    {
        $this->callee = $callee;
        $this->caller = $caller;
    }

    public function getCallee(): string
    {
        return $this->callee;
    }

    public function getCaller(): ?string
    {
        return $this->caller;
    }

    public function getType(): string
    {
        return DependencyGraph::TYPE_METHOD_CALL;
    }

    public function isEqual(Base $that): bool
    {
        return (
            $that instanceof self &&
            $this->getCallee() === $that->getCallee() &&
            $this->getCaller() === $that->getCaller()
        );
    }

    public function toString(): string
    {
        $ret = "{$this->getType()}:{$this->getCaller()}";
        if ($this->getCaller()) {
            $ret .= ":{$this->getCaller()}";
        }

        return $ret;
    }
}
