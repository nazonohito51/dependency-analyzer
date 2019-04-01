<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use PHPStan\Reflection\ReflectionWithFilename;

class UnknownClassReflection implements ReflectionWithFilename
{
    /**
     * @var string
     */
    private $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getFileName(): string
    {
        return 'unknown';
    }

    public function getDisplayName(): string
    {
        return $this->className;
    }
}
