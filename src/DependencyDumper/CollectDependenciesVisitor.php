<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\DependencyGraphBuilder;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use PHPStan\Analyser\Scope;

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

    public function __invoke(\PhpParser\Node $node, Scope $scope): void
    {
        // TODO: collect dependencies to file cache for improve performance

        try {
            $this->dependencyResolver->resolveDependencies($node, $scope, $this->dependencyGraphBuilder);
//            foreach ($this->dependencyResolver->resolveDependencies($node, $scope, $this->dependencyGraphBuilder) as $dependeeReflection) {
//                if ($dependeeReflection instanceof ClassReflection) {
////                    if ($node instanceof MethodCall && $scope->getFunction()) {
////                        $dependeeClass = $dependeeReflection;
////                        $dependeeFunction = $node->name->name;
////
////                        $depender = $scope->getClassReflection();
////                        $dependerFunction = $scope->getFunction();
////                    }
//                    $this->addDependency($node, $scope, $dependeeReflection);
//                } elseif ($dependeeReflection instanceof UnknownClassReflection) {
//                    $this->addUnknownDependency($node, $scope, $dependeeReflection);
//                } elseif ($dependeeReflection instanceof PhpFunctionReflection) {
//                    // function call
//                    // ex:
//                    //   array_map(...);
//                    //   var_dump(...);
//                } else {
//                    // error of DependencyResolver
//                    throw new ResolveDependencyException($node, 'resolving node dependency is failed.');
//                }
//            }
        } catch (ResolveDependencyException $e) {
            DependencyDumper::getObserver()->notifyResolveDependencyError($this->file, $e);
        }
    }

    public function getDependencyGraphBuilder(): DependencyGraphBuilder
    {
        return $this->dependencyGraphBuilder;
    }
}
