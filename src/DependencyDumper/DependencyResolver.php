<?php
declare(strict_types = 1);

namespace DependencyAnalyzer\DependencyDumper;

use DependencyAnalyzer\DependencyGraphBuilder;
use DependencyAnalyzer\Exceptions\ResolveDependencyException;
use PhpParser\Node\Identifier;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionWithFilename;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ClosureType;
use PHPStan\Type\TypeWithClassName;

/**
 * @da-internal \DependencyAnalyzer\DependencyDumper\
 */
class DependencyResolver
{
    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var Lexer
     */
    protected $phpDocLexer;

    /**
     * @var PhpDocParser
     */
    protected $phpDocParser;

    /**
     * @var DependencyGraphBuilder
     */
    protected $dependencyGraphBuilder;

    /**
     * @var \ReflectionClass
     */
    protected $depender = null;

    public function __construct(Broker $broker, Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->broker = $broker;
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }

    protected function getDependerReflection(\PhpParser\Node $node, Scope $scope): ?ClassReflection
    {
        if ($scope->isInClass()) {
            return $scope->getClassReflection();
        } else {
            // Maybe, class declare statement
            // ex:
            //   class Hoge {}
            //   abstract class Hoge {}
            //   interface Hoge {}
            if ($node instanceof \PhpParser\Node\Stmt\ClassLike) {
                return $this->resolveClassReflection($node->namespacedName->toString());
            }
        }

        return null;
    }

    /**
     * @param \PhpParser\Node $node
     * @param Scope $scope
     * @param DependencyGraphBuilder $dependencyGraphBuilder
     * @return ReflectionWithFilename[]
     */
    public function resolveDependencies(\PhpParser\Node $node, Scope $scope, DependencyGraphBuilder $dependencyGraphBuilder): array
    {
        try {
            if (is_null($this->depender = $this->getDependerReflection($node, $scope))) {
                return [];
            }
            $this->depender = $this->depender->getNativeReflection();
            $this->dependencyGraphBuilder = $dependencyGraphBuilder;

            if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                // define class statement
                // ex: class SomeClass {}
                $this->resolveClassNode($node);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
                // define interface statement
                // ex: interface SomeInterface {}
                $this->resolveInterfaceNode($node);
            } elseif ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                // define class method statement
                // ex:
                //   class SomeClass {
                //       function ClassMethod() {}
                //   }
                $this->resolveClassMethod($node, $scope);
//            } elseif ($node instanceof \PhpParser\Node\Stmt\Function_) {
//                // define function statement
//                // ex: function SomeFunction() {}
//                return $this->resolveFunction($node);
            } elseif ($node instanceof \PhpParser\Node\Expr\Closure) {
                // closure expression
                // ex:
                //   function (SomeClass1 $someClass1): SomeClass2 {
                //       // some logic.
                //   }
                $this->resolveClosure($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\FuncCall) {
                // function call expression
                // ex: someFunction();
                $this->resolveFuncCall($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\MethodCall) {
                // method call expression
                // ex: $someObject->someMethod();
                $this->resolveMethodCall($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
                // property fetch expression
                // ex: $someObject->somePublicProperty;
                $this->resolvePropertyFetch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\StaticCall) {
                // static method call expression
                // ex: SomeClass::someStaticMethod()
                $this->resolveStaticCall($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                // class const fetch expression
                // ex: SomeClass::SOME_CONST
                $this->resolveClassConstFetch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                // class static property fetch expression
                // ex: SomeClass::$someStaticProperty
                $this->resolveStaticPropertyFetch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\New_) {
                // new object expression
                // ex: new SomeClass();
                $this->resolveNew($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\TraitUse) {
                // use trait expression
                // ex:
                //   class SomeClass {
                //     use SomeTrait;
                //   }
                $this->resolveTraitUse($node);
            } elseif ($node instanceof \PhpParser\Node\Expr\Instanceof_) {
                // instanceof expression
                // ex: if ($someObject instanceof SomeClass) {}
                $this->resolveInstanceOf($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Catch_) {
                // catch expression
                // ex: try {} catch (SomeException $e) {}
                $this->resolveCatch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
                // array dim fetch expression
                // ex: $someArray[2]
                $this->resolveArrayDimFetch($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
                // foreach access expression
                // foreach ($someArray as $key => $someObject) {}
                $this->resolveForeach($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Expr\Array_) {
                $this->resolveArray($node, $scope);
            } elseif ($node instanceof \PhpParser\Node\Stmt\PropertyProperty) {
                // property with phpdoc
                // ex:
                //   class SomeClass {
                //     /** @var SomeClass2 $someProperty */
                //     private $someProperty;
                //   }
                $this->resolvePropertyProperty($node, $scope);
            }
        } catch (AnalyzedCodeException $e) {
            throw new ResolveDependencyException($node, 'resolving node dependency is failed.', 0, $e);
        } catch (ShouldNotHappenException $e) {
            throw new ResolveDependencyException($node, 'resolving node dependency is failed.', 0, $e);
        }

        return [];
    }

    public function resolveClassReflection(string $className): ?ClassReflection
    {
        try {
            return $this->broker->getClass($className);
        } catch (\PHPStan\Broker\ClassNotFoundException $e) {
            return null;
//            return new UnknownClassReflection($className);
        }
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
     * @return ClassReflection[]
     */
    protected function extractFromParametersAcceptor(ParametersAcceptorWithPhpDocs $parametersAcceptor): array
    {
        $dependenciesReflections = [];

        foreach ($parametersAcceptor->getParameters() as $parameter) {
            $referencedClasses = array_merge(
                $parameter->getNativeType()->getReferencedClasses(),
                $parameter->getPhpDocType()->getReferencedClasses()
            );

            foreach ($referencedClasses as $referencedClass) {
                $dependenciesReflections[] = $this->resolveClassReflection($referencedClass);
            }
        }

        $returnTypeReferencedClasses = array_merge(
            $parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
            $parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
        );
        foreach ($returnTypeReferencedClasses as $referencedClass) {
            $dependenciesReflections[] = $this->resolveClassReflection($referencedClass);
        }

        return $dependenciesReflections;
    }

    protected function resolveClassReflectionOrAddUnkownDependency(string $className): ?ClassReflection
    {
        if (!is_null($classReflection = $this->resolveClassReflection($className))) {
            return $classReflection;
        }

        $this->dependencyGraphBuilder->addUnknownDependency($this->depender, $className);
        return null;
    }

    protected function addDependencyWhenResolveClassReflectionIsSucceeded(string $className): void
    {
        if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($className)) {
            $this->dependencyGraphBuilder->addDependency($this->depender, $dependee->getNativeReflection());
        }
    }

    protected function resolveClassNode(\PhpParser\Node\Stmt\Class_ $node): void
    {
        if ($node->extends !== null) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($node->extends->toString())) {
                $this->dependencyGraphBuilder->addExtends($this->depender, $dependee->getNativeReflection());
            }
        }
        foreach ($node->implements as $className) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($className->toString())) {
                $this->dependencyGraphBuilder->addImplements($this->depender, $dependee->getNativeReflection());
            }
        }
        if ($node->getDocComment() !== null) {
            $tokens = new TokenIterator($this->phpDocLexer->tokenize($node->getDocComment()->getText()));
            $phpDocNode = $this->phpDocParser->parse($tokens);
            foreach ($phpDocNode->getTagsByName('@dependOn') as $phpDocTagNode) {
                /** @var PhpDocTagNode $phpDocTagNode */
                preg_match('/^@dependOn\s+(.+)$/', $phpDocTagNode->__toString(), $matches);

                $this->addDependencyWhenResolveClassReflectionIsSucceeded($matches[1]);
            };
        }
    }

    protected function resolveInterfaceNode(\PhpParser\Node\Stmt\Interface_ $node): void
    {
        if ($node->extends !== null) {
            foreach ($node->extends as $className) {
                if ($dependee = $this->resolveClassReflection($className->toString())) {
                    $this->dependencyGraphBuilder->addExtends($this->depender, $dependee->getNativeReflection());
                }
            }
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\ClassMethod $node
     * @param Scope $scope
     * @throws \PHPStan\Reflection\MissingMethodFromReflectionException
     */
    protected function resolveClassMethod(\PhpParser\Node\Stmt\ClassMethod $node, Scope $scope)
    {
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }

        $nativeMethod = $scope->getClassReflection()->getNativeMethod($node->name->name);
        if ($nativeMethod instanceof PhpMethodReflection) {
            /** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($nativeMethod->getVariants());

//            foreach ($this->extractFromParametersAcceptor($parametersAcceptor) as $classReflection) {
//                $this->dependencyGraphBuilder->addDependency($this->depender, $classReflection->getNativeReflection());
//            }

            foreach ($parametersAcceptor->getParameters() as $parameter) {
                $referencedClasses = array_merge(
                    $parameter->getNativeType()->getReferencedClasses(),
                    $parameter->getPhpDocType()->getReferencedClasses()
                );

                foreach ($referencedClasses as $referencedClass) {
                    $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
                }
            }

            $returnTypeReferencedClasses = array_merge(
                $parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
                $parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
            );
            foreach ($returnTypeReferencedClasses as $referencedClass) {
                $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
            }
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\Function_ $node
     * @return ReflectionWithFilename[]
     * @throws \PHPStan\Broker\FunctionNotFoundException
     */
    protected function resolveFunction(\PhpParser\Node\Stmt\Function_ $node): array
    {
        $functionName = $node->name->name;
        if (isset($node->namespacedName)) {
            $functionName = (string)$node->namespacedName;
        }
        $functionNameName = new \PhpParser\Node\Name($functionName);
        if ($this->broker->hasCustomFunction($functionNameName, null)) {
            $functionReflection = $this->broker->getCustomFunction($functionNameName, null);

            /** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants());
            return $this->extractFromParametersAcceptor($parametersAcceptor);
        }

        return [];
    }

    protected function resolveClosure(\PhpParser\Node\Expr\Closure $node, Scope $scope)
    {
        /** @var ClosureType $closureType */
        $closureType = $scope->getType($node);
        foreach ($closureType->getParameters() as $parameter) {
            $referencedClasses = $parameter->getType()->getReferencedClasses();
            foreach ($referencedClasses as $referencedClass) {
                $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
            }
        }

        $returnTypeReferencedClasses = $closureType->getReturnType()->getReferencedClasses();
        foreach ($returnTypeReferencedClasses as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveFuncCall(\PhpParser\Node\Expr\FuncCall $node, Scope $scope)
    {
//        $functionName = $node->name;
//        if ($functionName instanceof \PhpParser\Node\Name) {
//            try {
//                $dependenciesReflections[] = $this->getFunctionReflection($functionName, $scope);
//            } catch (\PHPStan\Broker\FunctionNotFoundException $e) {
//                // pass
//            }
//        } else {
//            $variants = $scope->getType($functionName)->getCallableParametersAcceptors($scope);
//            foreach ($variants as $variant) {
//                $referencedClasses = $variant->getReturnType()->getReferencedClasses();
//                foreach ($referencedClasses as $referencedClass) {
//                    $dependenciesReflections[] = $this->resolveClassReflection($referencedClass);
//                }
//            }
//        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveMethodCall(\PhpParser\Node\Expr\MethodCall $node, Scope $scope)
    {
        if ($node instanceof Identifier) {
            $classNames = $scope->getType($node->var)->getReferencedClasses();
            foreach ($classNames as $className) {
                if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($className)) {
                    $this->dependencyGraphBuilder->addMethodCall(
                        $this->depender,
                        $dependee->getNativeReflection(),
                        $node->name->toString(),
                        $scope->getFunctionName()
                    );
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolvePropertyFetch(\PhpParser\Node\Expr\PropertyFetch $node, Scope $scope)
    {
        if ($node->name instanceof Identifier) {
            $classNames = $scope->getType($node->var)->getReferencedClasses();
            foreach ($classNames as $className) {
                if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($className)) {
                    $this->dependencyGraphBuilder->addPropertyFetch(
                        $this->depender,
                        $dependee->getNativeReflection(),
                        $node->name->toString(),
                        $scope->getFunctionName()
                    );
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveStaticCall(\PhpParser\Node\Expr\StaticCall $node, Scope $scope)
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($scope->resolveName($node->class))) {
                $this->dependencyGraphBuilder->addMethodCall($this->depender, $dependee->getNativeReflection(), $node->name->toString(), $scope->getFunctionName());
            }
        } else {
            foreach ($scope->getType($node->class)->getReferencedClasses() as $referencedClass) {
                if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($referencedClass)) {
                    $this->dependencyGraphBuilder->addMethodCall($this->depender, $dependee->getNativeReflection(), $node->name->toString(), $scope->getFunctionName());
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $node, Scope $scope)
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($scope->resolveName($node->class))) {
                $this->dependencyGraphBuilder->addConstFetch(
                    $this->depender,
                    $dependee->getNativeReflection(),
                    $node->name->toString(),
                    $scope->getFunctionName()
                );
            }
        } else {
            foreach ($scope->getType($node->class)->getReferencedClasses() as $referencedClass) {
                if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($referencedClass)) {
                    $this->dependencyGraphBuilder->addConstFetch($this->depender, $dependee->getNativeReflection(), $node->name->toString(), $scope->getFunctionName());
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveStaticPropertyFetch(\PhpParser\Node\Expr\StaticPropertyFetch $node, Scope $scope)
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($scope->resolveName($node->class))) {
                $this->dependencyGraphBuilder->addPropertyFetch($this->depender, $dependee->getNativeReflection(), $node->name->toString(), $scope->getFunctionName());
            }
        } else {
            foreach ($scope->getType($node->class)->getReferencedClasses() as $referencedClass) {
                if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($scope->resolveName($node->class))) {
                    $this->dependencyGraphBuilder->addPropertyFetch($this->depender, $dependee->getNativeReflection(), $node->name->toString(), $scope->getFunctionName());
                }
            }
        }

        $returnType = $scope->getType($node);
        foreach ($returnType->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveNew(\PhpParser\Node\Expr\New_ $node, Scope $scope)
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($scope->resolveName($node->class))) {
                $this->dependencyGraphBuilder->addNew($this->depender, $dependee->getNativeReflection(), $scope->getFunctionName());
            }
        }
    }

    protected function resolveTraitUse(\PhpParser\Node\Stmt\TraitUse $node)
    {
        foreach ($node->traits as $traitName) {
            if ($dependee = $this->resolveClassReflectionOrAddUnkownDependency($traitName->toString())) {
                $this->dependencyGraphBuilder->addUseTrait($this->depender, $dependee->getNativeReflection());
            }
        }
    }

    protected function resolveInstanceOf(\PhpParser\Node\Expr\Instanceof_ $node, Scope $scope)
    {
        if ($node->class instanceof \PhpParser\Node\Name) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($scope->resolveName($node->class));
        }
    }

    protected function resolveCatch(\PhpParser\Node\Stmt\Catch_ $node, Scope $scope)
    {
        foreach ($node->types as $type) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($scope->resolveName($type));
        }
    }

    protected function resolveArrayDimFetch(\PhpParser\Node\Expr\ArrayDimFetch $node, Scope $scope)
    {
        if ($node->dim !== null) {
            $varType = $scope->getType($node->var);
            $dimType = $scope->getType($node->dim);

            foreach ($varType->getOffsetValueType($dimType)->getReferencedClasses() as $referencedClass) {
                $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
            }
        }
    }

    protected function resolveForeach(\PhpParser\Node\Stmt\Foreach_ $node, Scope $scope)
    {
        $exprType = $scope->getType($node->expr);
        if ($node->keyVar !== null) {
            foreach ($exprType->getIterableKeyType()->getReferencedClasses() as $referencedClass) {
                $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
            }
        }

        foreach ($exprType->getIterableValueType()->getReferencedClasses() as $referencedClass) {
            $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
        }
    }

    protected function resolveArray(\PhpParser\Node\Expr\Array_ $node, Scope $scope)
    {
        $arrayType = $scope->getType($node);
        if (!$arrayType->isCallable()->no()) {
            foreach ($arrayType->getCallableParametersAcceptors($scope) as $variant) {
                $referencedClasses = $variant->getReturnType()->getReferencedClasses();
                foreach ($referencedClasses as $referencedClass) {
                    $this->addDependencyWhenResolveClassReflectionIsSucceeded($referencedClass);
                }
            }
        }
    }

    /**
     * @param \PhpParser\Node\Stmt\PropertyProperty $node
     * @param Scope $scope
     * @throws \PHPStan\Reflection\MissingPropertyFromReflectionException
     */
    protected function resolvePropertyProperty(\PhpParser\Node\Stmt\PropertyProperty $node, Scope $scope)
    {
        if (!$scope->isInClass()) {
            throw new \PHPStan\ShouldNotHappenException();
        }
        $nativeProperty = $scope->getClassReflection()->getNativeProperty($node->name->name);
        if ($nativeProperty instanceof PhpPropertyReflection) {
            $type = $nativeProperty->getType();
            if ($type instanceof TypeWithClassName) {
                $this->addDependencyWhenResolveClassReflectionIsSucceeded($type->getClassName());
            }
        }
    }
}
