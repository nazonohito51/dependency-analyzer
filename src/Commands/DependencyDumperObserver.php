<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyDumper\ObserverInterface;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\Exceptions\AnalyzedFileException;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use Symfony\Component\Console\Output\OutputInterface;

class DependencyDumperObserver implements ObserverInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $max = 0;

    /**
     * @var int
     */
    private $counter = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function start(int $max): void
    {
        $this->counter = 0;
        $this->max = $max;
    }

    public function end(): void
    {
        return;
    }

    public function update(string $currentFile): void
    {
        $this->counter++;

        $this->output->writeln("Analyze start({$this->counter}/{$this->max}): {$currentFile}");
    }

    public function notifyAnalyzeFileError(AnalyzedFileException $e): void
    {
        $this->output->writeln("Error: analysing file is failed. file is {$e->getAnalyzedFile()}.");

        if ($this->output->isVerbose()) {
            $this->output->writeln("exception: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
        }
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln("detail of exception: {$e}");
        }
    }

    public function notifyResolveDependencyError(string $file, ResolveDependencyException $e): void
    {
        $this->output->writeln("Error: resolving dependency is failed, node_type:{$e->getNodeType()} in {$file}:{$e->getNodeLine()}");
        $this->output->writeln('Skip analysing this file, therefore result of analyze of this file is incomplete.');

        if ($this->output->isVerbose()) {
            $this->output->writeln("exception: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
        }
        if ($this->output->isVeryVerbose()) {
            $this->output->writeln("detail of exception: {$e}");
        }
    }

    public function notifyResolvePhpDocError(string $file, string $fqsen, InvalidFullyQualifiedStructureElementNameException $e): void
    {
        $this->output->writeln("Error: resolving phpdoc is failed, {$fqsen} in {$file}. Because element name is invalid: {$e->getInvalidElementName()}");
        $this->output->writeln('Skip analysing this phpdoc, therefore result of analyze of this file is incomplete.');
    }
}
