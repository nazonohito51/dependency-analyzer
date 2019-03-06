<?php
declare(strict_types = 1);

namespace DependencyAnalyzer\Commands;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DirectedGraph;
use DependencyAnalyzer\Exceptions\InvalidCommandArgumentException;
use DependencyAnalyzer\Exceptions\UnexpectedException;
use Nette\DI\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileFinder;
use PHPStan\File\FileHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AnalyzeDependencyCommand extends \Symfony\Component\Console\Command\Command
{
    protected const NAME = '';
    protected const DESCRIPTION = '';

    protected abstract function inspectGraph(DirectedGraph $graph): int;

    /**
     * @var string[]
     */
    protected $paths = [];

    /**
     * @var Container $container
     */
    protected $container;

    protected function configure(): void
    {
        $this->setName(static::NAME)
            ->setDescription(static::DESCRIPTION)
            ->setDefinition([
                new InputArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Target directory of analyze'),
                new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the run (ex: 500k, 500M, 5G)'),
            ]);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->paths = $this->getAbsolutePaths($input);
        $this->container = $this->createPHPStanContainer($this->paths);

        if ($memoryLimit = $input->getOption('memory-limit')) {
            $this->setMemoryLimit($memoryLimit);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dependencyDumper = $this->container->getByType(DependencyDumper::class);
        $allFiles = $this->getAllFilePaths();

        $dependencies = $dependencyDumper->dump($allFiles);

        return $this->inspectGraph($dependencies);
    }

    /**
     * @param InputInterface $input
     * @return string[]
     */
    protected function getAbsolutePaths(InputInterface $input): array
    {
        $paths = $input->getArgument('paths');
        $fileHelper = new FileHelper($this->getCurrentDir());
        $paths = array_map(function (string $path) use ($fileHelper): string {
            return $fileHelper->absolutizePath($path);
        }, $paths);

        return $paths;
    }

    /**
     * @return string[]
     */
    protected function getAllFilePaths(): array
    {
        $fileFinder = $this->container->getByType(FileFinder::class);

        try {
            $fileFinderResult = $fileFinder->findFiles($this->paths);
        } catch (\PHPStan\File\PathNotFoundException $e) {
            throw new InvalidCommandArgumentException('path was not found: ' . $e->getPath());
        }

        return $fileFinderResult->getFiles();
    }

    protected function createPHPStanContainer(array $paths): Container
    {
        $currentWorkingDirectory = $this->getCurrentDir();

        $tmpDir = sys_get_temp_dir() . '/phpstan';
        if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
            throw new UnexpectedException('creating a temp directory is failed: ' . $tmpDir);
        }

        $additionalConfigFiles = [realpath(__DIR__ . '/../../conf/config.neon')];

        return (new ContainerFactory($currentWorkingDirectory))->create($tmpDir, $additionalConfigFiles, $paths);
    }

    /**
     * @return string
     */
    protected function getCurrentDir(): string
    {
        $currentWorkingDirectory = getcwd();
        if ($currentWorkingDirectory === false) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        return $currentWorkingDirectory;
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
