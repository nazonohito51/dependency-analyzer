<?php
declare(strict_types = 1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use DependencyAnalyzer\Exceptions\ShouldNotHappenException;
use DependencyAnalyzer\Exceptions\UnexpectedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AnalyzeDependencyCommand extends Command
{
    const DEFAULT_CONFIG_FILES = [__DIR__ . '/../../conf/config.neon'];

    protected abstract function inspectDependencyGraph(DependencyGraph $graph): int;
    protected abstract function getCommandName(): string;
    protected abstract function getCommandDescription(): string;

    protected function configure(): void
    {
        $this->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->setDefinition([
                new InputArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Target directory of analyze'),
                new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the run (ex: 500k, 500M, 5G)'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($memoryLimit = $input->getOption('memory-limit')) {
            $this->setMemoryLimit($memoryLimit);
        }

        $dependencyGraph = $this->createDependencyGraph($input->getArgument('paths'));

        return $this->inspectDependencyGraph($dependencyGraph);
    }

    /**
     * @param string[] $paths
     * @return DependencyGraph
     */
    protected function createDependencyGraph(array $paths): DependencyGraph
    {
        $paths = array_map(function ($path) {
            $realpath = realpath($path);
            if (!is_file($realpath) && !is_dir($realpath)) {
                throw new InvalidCommandArgumentException("path was not found: {$realpath}");
            }

            return $realpath;
        }, $paths);

        return $this->createDependencyDumper()->dump($paths);
    }

    /**
     * @return DependencyDumper
     */
    protected function createDependencyDumper(): DependencyDumper
    {
        $currentWorkingDirectory = getcwd();
        if ($currentWorkingDirectory === false) {
            throw new ShouldNotHappenException('getting current working dir is failed.');
        }

        $tmpDir = sys_get_temp_dir() . '/phpstan';
        if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
            throw new ShouldNotHappenException('creating a temp directory is failed: ' . $tmpDir);
        }

        return DependencyDumper::createFromConfig($currentWorkingDirectory, $tmpDir, self::DEFAULT_CONFIG_FILES);
    }

    /**
     * @param string $memoryLimit
     */
    protected function setMemoryLimit(string $memoryLimit): void
    {
        if (preg_match('#^-?\d+[kMG]?$#i', $memoryLimit) !== 1) {
            throw new InvalidCommandArgumentException(sprintf('memory-limit is invalid format "%s".', $memoryLimit));
        }
        if (ini_set('memory_limit', $memoryLimit) === false) {
            throw new UnexpectedException("setting memory_limit to {$memoryLimit} is failed.");
        }
    }
}
