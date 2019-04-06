<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\DependencyGraphBuilder;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;

/**
 * @canOnlyUsedBy \DependencyAnalyzer\DependencyDumper
 */
class CollectDependenciesVisitor
{
    /**
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * @var DependencyGraphBuilder
     */
    protected $dependencyGraphBuilder;

    /**
     * @var ObserverInterface
     */
    protected $observer;

    /**
     * @var string
     */
    protected $file = null;

    public function __construct(DependencyResolver $dependencyResolver, DependencyGraphBuilder $dependencyGraphBuilder)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->dependencyGraphBuilder = $dependencyGraphBuilder;
    }

    public function setFile(string $file)
    {
        $this->file = $file;
    }

    public function setObserver(ObserverInterface $observer = null)
    {
        $this->observer = $observer;
    }

    public function __invoke(\PhpParser\Node $node, Scope $scope): void
    {
        try {
            foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependeeReflection) {
                if ($dependeeReflection instanceof ClassReflection) {
                    $this->addDependency($node, $scope, $dependeeReflection);
                } elseif ($dependeeReflection instanceof UnknownClassReflection) {
                    $this->addUnknownDependency($node, $scope, $dependeeReflection);
                } elseif ($dependeeReflection instanceof PhpFunctionReflection) {
                    // function call
                    // ex:
                    //   array_map(...);
                    //   var_dump(...);
                } else {
                    // error of DependencyResolver
                    throw new ResolveDependencyException($node, 'resolving node dependency is failed.');
                }
            }
        } catch (ResolveDependencyException $e) {
            // TODO: error handling... But, don't throw Exception, because NodeScopeResolver will die.
            if ($this->observer) {
                $this->observer->notifyResolveDependencyError($this->file, $e);
            }
        }
    }

    protected function addDependency(\PhpParser\Node $node, Scope $scope, ClassReflection $dependeeReflection): void
    {
        if ($scope->isInClass()) {
            if ($scope->getClassReflection()->getDisplayName() === $dependeeReflection->getDisplayName()) {
                // call same class method/property
            } else {
                $this->dependencyGraphBuilder->addDependency($scope->getClassReflection(), $dependeeReflection);
            }
        } else {
            // Maybe, class declare statement
            // ex:
            //   class Hoge {}
            //   abstract class Hoge {}
            //   interface Hoge {}
            if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
                $dependerReflection = $this->dependencyResolver->resolveClassReflection($node->namespacedName->toString());
                if ($dependerReflection instanceof ClassReflection) {
                    $this->dependencyGraphBuilder->addDependency($dependerReflection, $dependeeReflection);
                } else {
                    throw new ResolveDependencyException($node, 'resolving node dependency is failed, because unexpected node.');
                }
            }
        }
    }

    protected function addUnknownDependency(\PhpParser\Node $node, Scope $scope, UnknownClassReflection $classReflection)
    {
        if ($scope->isInClass()) {
            $this->dependencyGraphBuilder->addUnknownDependency($scope->getClassReflection(), $classReflection->getDisplayName());
        }
    }

    public function createDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraphBuilder->build();
    }
}
