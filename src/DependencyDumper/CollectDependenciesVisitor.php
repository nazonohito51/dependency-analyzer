<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraphBuilder;
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
            foreach ($this->dependencyResolver->resolveDependencies($node, $scope, $this->dependencyGraphBuilder) as $dependeeReflection) {
                if ($node instanceof \PhpParser\Node\Stmt\Class_ ||
                    $node instanceof \PhpParser\Node\Stmt\Interface_ ||
                    $node instanceof \PhpParser\Node\Stmt\ClassMethod ||
                    $node instanceof \PhpParser\Node\Expr\Closure ||
                    $node instanceof \PhpParser\Node\Expr\FuncCall) {
                    continue;
                }

                if ($dependeeReflection instanceof ClassReflection) {
//                    if ($node instanceof MethodCall && $scope->getFunction()) {
//                        $dependeeClass = $dependeeReflection;
//                        $dependeeFunction = $node->name->name;
//
//                        $depender = $scope->getClassReflection();
//                        $dependerFunction = $scope->getFunction();
//                    }
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
                $this->dependencyGraphBuilder->addDependency(
                    $scope->getClassReflection()->getNativeReflection(),
                    $dependeeReflection->getNativeReflection()
                );
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
                    $this->dependencyGraphBuilder->addDependency(
                        $dependerReflection->getNativeReflection(),
                        $dependeeReflection->getNativeReflection()
                    );
                } else {
                    throw new ResolveDependencyException($node, 'resolving node dependency is failed, because unexpected node.');
                }
            }
        }
    }

    protected function addUnknownDependency(\PhpParser\Node $node, Scope $scope, UnknownClassReflection $classReflection)
    {
        if ($scope->isInClass()) {
            $this->dependencyGraphBuilder->addUnknownDependency(
                $scope->getClassReflection()->getNativeReflection(),
                $classReflection->getDisplayName()
            );
        }
    }

    public function createDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraphBuilder->build();
    }
}
