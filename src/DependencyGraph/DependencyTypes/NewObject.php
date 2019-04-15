<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

use DependencyAnalyzer\DependencyGraph;

class NewObject extends Base
{
    /**
     * @var string
     */
    private $caller;

    public function __construct(string $caller = null)
    {
        $this->caller = $caller;
    }

    public function getCaller(): ?string
    {
        return $this->caller;
    }

    public function getType(): string
    {
        return DependencyGraph::TYPE_NEW;
    }

    public function isEqual(Base $that): bool
    {
        return (
            $that instanceof self &&
            $this->getCaller() === $that->getCaller()
        );
    }
}
