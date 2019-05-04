<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

class DepsInternal
{
    const TAG_NAME = '@deps-internal';

    /**
     * @var FQSEN
     */
    protected $fqsen;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var FQSEN[]
     */
    protected $targets = [];

    /**
     * @param FQSEN $fqsen
     * @param string[] $options
     * @deps-internal \DependencyAnalyzer\DependencyGraphBuilder\ExtraPhpDocTagResolver
     */
    public function __construct(FQSEN $fqsen, array $options = [])
    {
        $this->fqsen = $fqsen;
        $this->options = $options;

        try {
            foreach ($this->options as $option) {
                $this->targets[] = FullyQualifiedStructuralElementName::createFromString($option);
            }
        } catch (InvalidFullyQualifiedStructureElementNameException $e) {
            throw $e;
        }
    }

    public function getFqsen(): FQSEN
    {
        return $this->fqsen;
    }

    /**
     * @return FQSEN[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    public function getTargetsAsString(): array
    {
        return $this->options;
    }
}
