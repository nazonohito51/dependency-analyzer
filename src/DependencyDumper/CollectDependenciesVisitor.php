<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use DependencyAnalyzer\Exceptions\ShouldNotHappenException;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class CollectDependenciesVisitor
{
    /**
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    protected $dependencies = [];

    public function __construct(DependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    public function __invoke(\PhpParser\Node $node, Scope $scope): void
    {
        try {
            foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
                if ($dependencyReflection instanceof ClassReflection) {
                    if ($scope->isInClass()) {
                        if ($scope->getClassReflection()->getDisplayName() === $dependencyReflection->getDisplayName()) {
                            // call same class method/property
                        } else {
                            $className = $scope->getClassReflection()->getDisplayName();
                            $this->addToDependencies($className, $dependencyReflection->getDisplayName());
                        }
                    } else {
                        // Maybe, class declare statement
                        // ex:
                        //   class Hoge {}
                        //   abstract class Hoge {}
                        //   interface Hoge {}
                        if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
                            $this->addToDependencies($node->namespacedName->toString(), $dependencyReflection->getDisplayName());
                        }
                    }
                } elseif ($dependencyReflection instanceof PhpFunctionReflection) {
                    // function call
                    // ex:
                    //   array_map(...);
                    //   var_dump(...);
                } else {
                    // error of DependencyResolver
                    throw new ShouldNotHappenException('resolving node dependency is failed.');
                }
            }
        } catch (ResolveDependencyException $e) {
            throw new ShouldNotHappenException('collecting dependencies is failed.', 0, $e);
        }
    }

    protected function addToDependencies(string $depender, string $dependee): void
    {
        if (!isset($this->dependencies[$depender])) {
            $this->dependencies[$depender] = [];
        }

        if (!in_array($dependee, $this->dependencies[$depender])) {
            $this->dependencies[$depender][] = $dependee;
        }
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
