<?php declare(strict_types = 1);

namespace DependencyAnalyzer;

use Nette\Utils\Json;
use PHPStan\Command\CommandHelper;
use PHPStan\Command\ErrorsConsoleStyle;
use PHPStan\Dependency\DependencyDumper;
use PHPStan\File\FileHelper;
use PHPStan\ShouldNotHappenException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeDependenciesCommand extends \Symfony\Component\Console\Command\Command
{
    private const NAME = 'analyze-deps';

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Analyze dependency tree')
            ->setDefinition([
                new InputArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Paths with source code to run dump on'),
                new InputOption('paths-file', null, InputOption::VALUE_REQUIRED, 'Path to a file with a list of paths to run analysis on'),
                new InputOption('configuration', 'c', InputOption::VALUE_REQUIRED, 'Path to project configuration file'),
                new InputOption(ErrorsConsoleStyle::OPTION_NO_PROGRESS, null, InputOption::VALUE_NONE, 'Do not show progress bar, only results'),
                new InputOption('autoload-file', 'a', InputOption::VALUE_REQUIRED, 'Project\'s additional autoload file path'),
                new InputOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the run'),
                new InputOption('analysed-paths', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Project-scope paths'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $inceptionResult = $this->buildInceptionResult($input, $output);
        } catch (\PHPStan\Command\InceptionNotSuccessfulException $e) {
            return 1;
        }

        $consoleStyle = $inceptionResult->getConsoleStyle();

        /** @var DependencyDumper $dependencyDumper */
        $dependencyDumper = $inceptionResult->getContainer()->getByType(DependencyDumper::class);

        /** @var FileHelper $fileHelper */
        $fileHelper = $inceptionResult->getContainer()->getByType(FileHelper::class);

        /** @var string[] $analysedPaths */
        $analysedPaths = $input->getOption('analysed-paths');
        $analysedPaths = array_map(static function (string $path) use ($fileHelper): string {
            return $fileHelper->absolutizePath($path);
        }, $analysedPaths);
        $dependencies = $dependencyDumper->dumpDependencies(
            $inceptionResult->getFiles(),
            static function (int $count) use ($consoleStyle): void {
                $consoleStyle->progressStart($count);
            },
            static function () use ($consoleStyle): void {
                $consoleStyle->progressAdvance();
            },
            count($analysedPaths) > 0 ? $analysedPaths : null
        );
        
        $errors = $this->verify();
        
        $consoleStyle->progressFinish();
        $consoleStyle->writeln(Json::encode($dependencies, Json::PRETTY));

        return $inceptionResult->handleReturn(0);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return \PHPStan\Command\InceptionResult
     * @throws \PHPStan\Command\InceptionNotSuccessfulException
     * @throws \PHPStan\ShouldNotHappenException
     */
    protected function buildInceptionResult(InputInterface $input, OutputInterface $output): \PHPStan\Command\InceptionResult
    {
        /** @var string[] $paths */
        $paths = $input->getArgument('paths');

        /** @var string|null $memoryLimit */
        $memoryLimit = $input->getOption('memory-limit');

        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption('autoload-file');

        /** @var string|null $configurationFile */
        $configurationFile = $input->getOption('configuration');

        /** @var string|null $pathsFile */
        $pathsFile = $input->getOption('paths-file');
        $inceptionResult = CommandHelper::begin(
            $input,
            $output,
            $paths,
            $pathsFile,
            $memoryLimit,
            $autoloadFile,
            $configurationFile,
            '0' // irrelevant but prevents an error when a config file is passed
        );
        return $inceptionResult;
    }

    protected function verify(): array
    {
        $errors = $this->verifyDirectedAcyclicGraph();

        return $errors;
    }

    protected function verifyDirectedAcyclicGraph()
    {
        // TODO: extract class
    }
}
