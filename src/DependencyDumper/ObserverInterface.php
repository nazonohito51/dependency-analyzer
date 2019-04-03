<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\Exceptions\AnalysedFileException;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;

interface ObserverInterface
{
    public function start(int $max): void;
    public function end(): void;

    public function update(string $currentFile);
    public function notifyAnalyzeFileError(AnalysedFileException $e);
    public function notifyResolveDependencyError(string $file, ResolveDependencyException $e);
}
