<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraph\ClassLike;
use DependencyAnalyzer\DependencyGraph\DependencyGraphFactory;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use DependencyAnalyzer\Exceptions\ShouldNotHappenException;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class CollectDependenciesVisitor
{
    /**
     * @var DependencyResolver
     */
    protected $dependencyResolver;

    /**
     * @var DependencyGraphFactory
     */
    protected $dependencyGraphFactory;

    public function __construct(DependencyResolver $dependencyResolver, DependencyGraphFactory $dependencyGraphFactory)
    {
        $this->dependencyResolver = $dependencyResolver;
        $this->dependencyGraphFactory = $dependencyGraphFactory;
    }

    public function __invoke(\PhpParser\Node $node, Scope $scope): void
    {
        try {
            foreach ($this->dependencyResolver->resolveDependencies($node, $scope) as $dependeeReflection) {
                if ($dependeeReflection instanceof ClassReflection) {
                    if ($scope->isInClass()) {
                        if ($scope->getClassReflection()->getDisplayName() === $dependeeReflection->getDisplayName()) {
                            // call same class method/property
                        } else {
                            $this->dependencyGraphFactory->addDependency($scope->getClassReflection(), $dependeeReflection);
//                            $this->addTest($scope->getClassReflection(), $dependeeReflection);
//
//                            $className = $scope->getClassReflection()->getDisplayName();
//                            $this->addToDependencies($className, $dependeeReflection->getDisplayName());
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
                                $this->dependencyGraphFactory->addDependency($dependerReflection, $dependeeReflection);
//                                $this->addTest($dependerReflection, $dependeeReflection);
                            } else {
                                throw new ShouldNotHappenException('resolving node dependency is failed.');
                            }
//
//                            $this->addToDependencies($node->namespacedName->toString(), $dependeeReflection->getDisplayName());
                        }
                    }
                } elseif ($dependeeReflection instanceof PhpFunctionReflection) {
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

//    protected function addToDependencies(string $depender, string $dependee): void
//    {
//        if (!isset($this->dependencies[$depender])) {
//            $this->dependencies[$depender] = [];
//        }
//
//        if (!in_array($dependee, $this->dependencies[$depender])) {
//            $this->dependencies[$depender][] = $dependee;
//        }
//    }
//
//    protected function addTest(ClassReflection $dependerReflection, ClassReflection $dependeeReflection)
//    {
//        if (is_null($classLike = $this->getTest($dependerReflection->getDisplayName()))) {
//            $this->test[] = $classLike = new ClassLike($dependerReflection);
//        }
//
//        $classLike->addDependee($dependeeReflection);
//    }
//
//    protected function getTest(string $dependerName)
//    {
//        foreach ($this->test as $item) {
//            if ($item->getName() === $dependerName) {
//                return $item;
//            }
//        }
//
//        return null;
//    }
//
//    /**
//     * @return ClassLike[]
//     */
//    public function getDependencies(): array
//    {
//        return $this->test;
//    }

    public function createDependencyGraph(): DependencyGraph
    {
        return $this->dependencyGraphFactory->create();
    }
}
