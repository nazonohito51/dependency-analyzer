<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;

class Internal extends Base
{
    protected const TAG_NAME = '@internal';

    /**
     * @var string
     */
    private $package;

    public function __construct(FQSEN $fqsen, string $package = null)
    {
        parent::__construct($fqsen);

        if (is_null($package)) {
            $package = $fqsen->getFullyQualifiedNamespaceName()->toString();
        }
        $this->package = $package;
    }

    public static function getTagName(): string
    {
        return self::TAG_NAME;
    }
}
