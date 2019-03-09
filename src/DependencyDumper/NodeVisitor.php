<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use PhpParser\Node;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class NodeVisitor
{
    /**
     * @var DependencyResolver
     */
    protected $nodeDependencyResolver;

    protected $dependencies = [];

    public function __construct(DependencyResolver $nodeDependencyResolver)
    {
        $this->nodeDependencyResolver = $nodeDependencyResolver;
    }

    public function __invoke(Node $node, Scope $scope): void
    {
        try {
            foreach ($this->nodeDependencyResolver->resolveDependencies($node, $scope) as $dependencyReflection) {
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
                        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                            $this->addToDependencies($node->namespacedName->toString(), $dependencyReflection->getDisplayName());
                        } elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                            $this->addToDependencies($node->namespacedName->toString(), $dependencyReflection->getDisplayName());
                        }
                    }
                } elseif ($dependencyReflection instanceof PhpFunctionReflection) {
                    // function call
                } else {
                    // error of DependencyResolver
                }
            }
        } catch (AnalysedCodeException $e) {
            // TODO: If there is file that can not is loaded.
        }
    }

    protected function addToDependencies(string $depender, string $dependee)
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
