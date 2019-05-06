<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

class DepsInternal extends Base
{
    protected const TAG_NAME = '@da-internal';

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
     * @inheritDoc
     */
    public function __construct(FQSEN $fqsen, array $options = [])
    {
        parent::__construct($fqsen);
        $this->options = $options;

        try {
            foreach ($this->options as $option) {
                $this->targets[] = FullyQualifiedStructuralElementName::createFromString($option);
            }
        } catch (InvalidFullyQualifiedStructureElementNameException $e) {
            throw $e;
        }
    }

    public static function getTagName(): string
    {
        return self::TAG_NAME;
    }

    /**
     * @return FQSEN[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @return string[]
     */
    public function getTargetsAsString(): array
    {
        return $this->options;
    }
}
