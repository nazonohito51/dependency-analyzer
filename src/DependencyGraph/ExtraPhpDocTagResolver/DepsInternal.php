<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;

class DepsInternal
{
    /**
     * @var FQSEN
     */
    protected $fqden;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var FQSEN[]
     */
    protected $targets = [];

    public function __construct(FQSEN $fqden, array $options = [])
    {
        $this->fqden = $fqden;
        $this->options = $options;

        foreach ($this->options as $option) {
            $this->targets[] = FQSEN::createFromString($option);
        }
    }

    public function getEqden(): FQSEN
    {
        return $this->fqden;
    }

    /**
     * @return FQSEN[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
