<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder;

class UnknownClassReflection
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string[]
     */
    private $dependerNames = [];

    public function __construct(string $className, string $file = null)
    {
        $this->className = $className;
        $this->file = $file;
    }

    public function getDisplayName(): string
    {
        return $this->className;
    }

    public function getFile(): ?string
    {
        $this->file;
    }

    public function getDependers()
    {
        return $this->dependerNames;
    }

    public function addDepender(string $className): void
    {
        if (!in_array($className, $this->dependerNames)) {
            $this->dependerNames[] = $className;
        }
    }

    public function mergeDepender(UnknownClassReflection $that)
    {
        foreach ($that->getDependers() as $depender) {
            $this->addDepender($depender);
        }
    }
}
