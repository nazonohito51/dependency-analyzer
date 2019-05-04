<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;

abstract class Base
{
    /**
     * @var FQSEN
     */
    protected $fqsen;

    /**
     * @param FQSEN $fqsen
     * @deps-internal \DependencyAnalyzer\DependencyGraphBuilder\ExtraPhpDocTagResolver
     * @deps-internal \DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags\
     */
    public function __construct(FQSEN $fqsen)
    {
        $this->fqsen = $fqsen;
    }

    public function getFqsen(): FQSEN
    {
        return $this->fqsen;
    }

    abstract public static function getTagName(): string;
}
