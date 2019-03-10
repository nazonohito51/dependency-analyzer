<?php
declare(strict_types = 1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionWithFilename;
use PHPStan\Type\ClosureType;
use PHPStan\Type\TypeWithClassName;

class DependencyResolver
{
    /**
     * @var Broker
     */
    protected $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    /**
     * @param \PhpParser\Node $node
     * @param Scope $scope
     * @return ReflectionWithFilename[]
     */
    public function resolveDependencies(\PhpParser\Node $node, Scope $scope): array
    {
        try {
            if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                return $this->resolveClassNode($node);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                return $this->resolveInterfaceNode($node);
            } elseif ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return $this->resolveClassMethod($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Function_) {
                return $this->resolveFunction($node);
            } elseif ($node instanceof \PhpParser\Node\Expr\Closure) {
                return $this->resolveClosure($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\FuncCall) {
                return $this->resolveFuncCall($node, $scope);
            } elseif (
                $node instanceof \PhpParser\Node\Expr\MethodCall ||
                $node instanceof \PhpParser\Node\Expr\PropertyFetch
            ) {
                return $this->resolveAccessClassElement($node, $scope);
            } elseif (
                $node instanceof \PhpParser\Node\Expr\StaticCall ||
                $node instanceof \PhpParser\Node\Expr\ClassConstFetch ||
                $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch
            ) {
                return $this->resolveAccessStaticClassElement($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\New_) {
                return $this->resolveNew($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\TraitUse) {
                return $this->resolveTraitUse($node);
            } elseif ($node instanceof \PhpParser\Node\Expr\Instanceof_) {
                return $this->resolveInstanceOf($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Catch_) {
                return $this->resolveCatch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
                return $this->resolveArrayDimFetch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
                return $this->resolveForeach($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\Array_) {
                return $this->resolveArray($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\PropertyProperty) {
                // TODO: Additional logic...
                return $this->resolvePropertyProperty($node, $scope);
            }
        } catch (AnalysedCodeException $e) {
            throw new ResolveDependencyException($node, 'resolving node dependency is failed.', 0, $e);
        }

        return [];
    }

    /**
     * @param string $className
     * @param ReflectionWithFilename[] $dependenciesReflections
     */
    protected function addClassToDependencies(string $className, array &$dependenciesReflections): void
    {
        try {
            $classReflection = $this->broker->getClass($className);
        } catch (\PHPStan\Broker\ClassNotFoundException $e) {
            return;
        }

        $dependenciesReflections[] = $classReflection;
    }

    protected function getFunctionReflection(\PhpParser\Node\Name $nameNode, ?Scope $scope): ReflectionWithFilename
    {
        $reflection = $this->broker->getFunction($nameNode, $scope);
        if (!$reflection instanceof ReflectionWithFilename) {
            throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
        }

        return $reflection;
    }

    /**
     * @param ParametersAcceptorWithPhpDocs $parametersAcceptor
     * @param ReflectionWithFilename[] $dependenciesReflections
     */
    protected function extractFromParametersAcceptor(
        ParametersAcceptorWithPhpDocs $parametersAcceptor,
        array &$dependenciesReflections
    ): void
    {
        foreach ($parametersAcceptor->getParameters() as $parameter) {
            $referencedClasses = array_merge(
                $parameter->getNativeType()->getReferencedClasses(),
                $parameter->getPhpDocType()->getReferencedClasses()
            );

            foreach ($referencedClasses as $referencedClass) {
                $this->addClassToDependencies($referencedClass, $dependenciesReflections);
            }
        }

        $returnTypeReferencedClasses = array_merge(
            $parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
            $parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
        );
        foreach ($returnTypeReferencedClasses as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     * @return mixed
     */
    protected function resolveClassNode(\PhpParser\Node\Stmt\Class_ $node)
    {
        $dependenciesReflections = [];

        if ($node->extends !== null) {
            $this->addClassToDependencies($node->extends->toString(), $dependenciesReflections);
        }
        foreach ($node->implements as $className) {
            $this->addClassToDependencies($className->toString(), $dependenciesReflections);
        }

        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\Interface_ $node
     * @return mixed
     */
    protected function resolveInterfaceNode(\PhpParser\Node\Stmt\Interface_ $node)
    {
        $dependenciesReflections = [];

        if ($node->extends !== null) {
            foreach ($node->extends as $className) {
                $this->addClassToDependencies($className->toString(), $dependenciesReflections);
            }
        }

        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @param Scope $scope
     * @return mixed
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     */
    protected function resolveClassMethod(\PhpParser\Node\Stmt\ClassMethod $node, Scope $scope)
    {
        $dependenciesReflections = [];

        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $nativeMethod = $scope->getClassReflection()->getNativeMethod($node->name->name);
        if ($nativeMethod instanceof PhpMethodReflection) {
            /** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($nativeMethod->getVariants());

            $this->extractFromParametersAcceptor($parametersAcceptor, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\Function_ $node
     * @return mixed
     * @throws \PHPStan\Broker\FunctionNotFoundException
     */
    protected function resolveFunction(\PhpParser\Node\Stmt\Function_ $node)
    {
        $dependenciesReflections = [];

        $functionName = $node->name->name;
        if (isset($node->namespacedName)) {
            $functionName = (string)$node->namespacedName;
        }
        $functionNameName = new \PhpParser\Node\Name($functionName);
        if ($this->broker->hasCustomFunction($functionNameName, null)) {
            $functionReflection = $this->broker->getCustomFunction($functionNameName, null);

            /** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants());
            $this->extractFromParametersAcceptor($parametersAcceptor, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\Closure $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveClosure(\PhpParser\Node\Expr\Closure $node, Scope $scope)
    {
        $dependenciesReflections = [];

        /** @var ClosureType $closureType */
        $closureType = $scope->getType($node);
        foreach ($closureType->getParameters() as $parameter) {
            $referencedClasses = $parameter->getType()->getReferencedClasses();
            foreach ($referencedClasses as $referencedClass) {
                $this->addClassToDependencies($referencedClass, $dependenciesReflections);
            }
        }

        $returnTypeReferencedClasses = $closureType->getReturnType()->getReferencedClasses();
        foreach ($returnTypeReferencedClasses as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\FuncCall $node
     * @param Scope $scope
     * @return array
     */
    protected function resolveFuncCall(\PhpParser\Node\Expr\FuncCall $node, Scope $scope): array
    {
        $dependenciesReflections = [];

        $functionName = $node->name;
        if ($functionName instanceof \PhpParser\Node\Name) {
            try {
                $dependenciesReflections[] = $this->getFunctionReflection($functionName, $scope);
            } catch (\PHPStan\Broker\FunctionNotFoundException $e) {
                // pass
            }
        } else {
            $variants = $scope->getType($functionName)->getCallableParametersAcceptors($scope);
            foreach ($variants as $variant) {
                $referencedClasses = $variant->getReturnType()->getReferencedClasses();
                foreach ($referencedClasses as $referencedClass) {
                    $this->addClassToDependencies($referencedClass, $dependenciesReflections);
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveAccessClassElement($node, Scope $scope)
    {
        $dependenciesReflections = [];
        $classNames = $scope->getType($node->var)->getReferencedClasses();
        foreach ($classNames as $className) {
            $this->addClassToDependencies($className, $dependenciesReflections);
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\StaticPropertyFetch $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveAccessStaticClassElement($node, Scope $scope)
    {
        $dependenciesReflections = [];
        if ($node->class instanceof \PhpParser\Node\Name) {
            $this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
        } else {
            foreach ($scope->getType($node->class)->getReferencedClasses() as $referencedClass) {
                $this->addClassToDependencies($referencedClass, $dependenciesReflections);
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\New_ $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveNew(\PhpParser\Node\Expr\New_ $node, Scope $scope)
    {
        $dependenciesReflections = [];
        if ($node->class instanceof \PhpParser\Node\Name) {
            $this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\TraitUse $node
     * @return mixed
     */
    protected function resolveTraitUse(\PhpParser\Node\Stmt\TraitUse $node)
    {
        $dependenciesReflections = [];
        foreach ($node->traits as $traitName) {
            $this->addClassToDependencies($traitName->toString(), $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\Instanceof_ $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveInstanceOf(\PhpParser\Node\Expr\Instanceof_ $node, Scope $scope)
    {
        $dependenciesReflections = [];
        if ($node->class instanceof \PhpParser\Node\Name) {
            $this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\Catch_ $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveCatch(\PhpParser\Node\Stmt\Catch_ $node, Scope $scope)
    {
        $dependenciesReflections = [];
        foreach ($node->types as $type) {
            $this->addClassToDependencies($scope->resolveName($type), $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveArrayDimFetch(\PhpParser\Node\Expr\ArrayDimFetch $node, Scope $scope)
    {
        $dependenciesReflections = [];
        if ($node->dim !== null) {
            $varType = $scope->getType($node->var);
            $dimType = $scope->getType($node->dim);

            foreach ($varType->getOffsetValueType($dimType)->getReferencedClasses() as $referencedClass) {
                $this->addClassToDependencies($referencedClass, $dependenciesReflections);
            }
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\Foreach_ $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveForeach(\PhpParser\Node\Stmt\Foreach_ $node, Scope $scope)
    {
        $dependenciesReflections = [];
        $exprType = $scope->getType($node->expr);
        if ($node->keyVar !== null) {
            foreach ($exprType->getIterableKeyType()->getReferencedClasses() as $referencedClass) {
                $this->addClassToDependencies($referencedClass, $dependenciesReflections);
            }
        }

        foreach ($exprType->getIterableValueType()->getReferencedClasses() as $referencedClass) {
            $this->addClassToDependencies($referencedClass, $dependenciesReflections);
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Expr\Array_ $node
     * @param Scope $scope
     * @return mixed
     */
    protected function resolveArray(\PhpParser\Node\Expr\Array_ $node, Scope $scope)
    {
        $dependenciesReflections = [];
        $arrayType = $scope->getType($node);
        if (!$arrayType->isCallable()->no()) {
            foreach ($arrayType->getCallableParametersAcceptors($scope) as $variant) {
                $referencedClasses = $variant->getReturnType()->getReferencedClasses();
                foreach ($referencedClasses as $referencedClass) {
                    $this->addClassToDependencies($referencedClass, $dependenciesReflections);
                }
            }
        }
        return $dependenciesReflections;
    }

    /**
     * @param \PhpParser\Node\Stmt\PropertyProperty $node
     * @param Scope $scope
     * @return mixed
     * @throws \PHPStan\Reflection\MissingPropertyFromReflectionException
     */
    protected function resolvePropertyProperty(\PhpParser\Node\Stmt\PropertyProperty $node, Scope $scope)
    {
        $dependenciesReflections = [];
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $nativeProperty = $scope->getClassReflection()->getNativeProperty($node->name->name);
        if ($nativeProperty instanceof PhpPropertyReflection) {
            $type = $nativeProperty->getType();
            if ($type instanceof TypeWithClassName) {
                $this->addClassToDependencies($type->getClassName(), $dependenciesReflections);
            }
        }
        return $dependenciesReflections;
    }

}
