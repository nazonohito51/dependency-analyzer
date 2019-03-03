<?php
declare(strict_types = 1);

namespace DependencyAnalyzer;

use DependencyAnalyzer\Detector\CycleDetector;
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

class AnalyzeDependenciesCommand extends \Symfony\Component\Console\Command\Command
{
    protected const NAME = 'analyze-deps';

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
        $this->setName(self::NAME)
            ->setDescription('Analyze dependency tree')
            ->setDefinition([
                new InputArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Target directory of analyze'),
                new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the run (ex: 500k, 500M, 5G)'),
            ]);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->paths = $this->getAbsolutePaths($input);
        $this->container = $this->createContainer($this->paths);

        if ($memoryLimit = $input->getOption('memory-limit')) {
            $this->setMemoryLimit($memoryLimit);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dependencyDumper = $this->container->getByType(DependencyDumper::class);
        $allFiles = $this->getAllFilePaths();

        $dependencies = $dependencyDumper->dump($allFiles);
        
        $errors = $this->verify($dependencies);
        var_dump($errors);

//        $output->writeln(Json::encode($dependencies, Json::PRETTY));
        var_dump($dependencies->toArray());
        var_dump(count($dependencies));
        $count = 0;
        foreach ($dependencies->toArray() as $depender => $dependees) {
            $count += count($dependees);
        }
        var_dump($count);

        return 0;
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

    protected function createContainer(array $paths)
    {
        $currentWorkingDirectory = $this->getCurrentDir();

        $tmpDir = sys_get_temp_dir() . '/phpstan';
        if (!@mkdir($tmpDir, 0777, true) && !is_dir($tmpDir)) {
            throw new UnexpectedException('creating a temp direcotry is failed: ' . $tmpDir);
        }

        $containerConfigFiles = [__DIR__ . '/../conf/config.neon'];

        return (new ContainerFactory($currentWorkingDirectory))->create($tmpDir, $containerConfigFiles, $paths);
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
            throw new InvalidCommandArgumentException('memory-limit is invalid format "%s".', $memoryLimit);
        }
        if (ini_set('memory_limit', $memoryLimit) === false) {
            throw new UnexpectedException("setting memory_limit to {$memoryLimit} is failed.");
        }
    }

    protected function verify(DirectedGraph $graph): array
    {
        $errors = $this->verifyDirectedAcyclicGraph($graph);

        return $errors;
    }

    protected function verifyDirectedAcyclicGraph(DirectedGraph $graph)
    {
        // TODO: extract class
        $detector = new CycleDetector();
        return $detector->inspect($graph);
    }
}
