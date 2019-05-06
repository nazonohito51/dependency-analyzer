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
    protected $targets;

    /**
     * @param FQSEN $fqsen
     * @param string[] $targets
     * @inheritDoc
     */
    public function __construct(FQSEN $fqsen, array $targets = [])
    {
        parent::__construct($fqsen);
        $this->targets = $targets;

        try {
            foreach ($this->targets as $target) {
                FullyQualifiedStructuralElementName::createFromString(substr($target, 0, 1) === '!' ? substr($target, 1) : $target);
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
     * @return string[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }
}
