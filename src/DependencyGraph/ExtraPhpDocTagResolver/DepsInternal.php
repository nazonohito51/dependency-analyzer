<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

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

        try {
            foreach ($this->options as $option) {
                $this->targets[] = FullyQualifiedStructuralElementName::createFromString($option);
            }
        } catch (InvalidFullyQualifiedStructureElementNameException $e) {
            throw $e;
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
