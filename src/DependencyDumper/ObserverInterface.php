<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\Exceptions\AnalyzedFileException;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;

interface ObserverInterface extends \DependencyAnalyzer\DependencyGraphBuilder\ObserverInterface
{
    public function start(int $max): void;
    public function end(): void;

    public function update(string $currentFile): void;
    public function notifyAnalyzeFileError(AnalyzedFileException $e): void;
    public function notifyResolveDependencyError(string $file, ResolveDependencyException $e): void;
}
