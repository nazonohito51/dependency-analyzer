<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraphBuilder;

use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

interface ObserverInterface
{
    public function notifyResolvePhpDocError(string $file, string $fqsen, InvalidFullyQualifiedStructureElementNameException $e): void;
}
