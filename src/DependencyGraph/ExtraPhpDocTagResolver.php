<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTagResolver\DepsInternal;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ClassReflection;

class ExtraPhpDocTagResolver
{
    const DEPS_INTERNAL = '@deps-internal';
    const ONLY_USED_BY_TAGS = '@canOnlyUsedBy';
    const DEPENDER_TAGS = '@dependee';
    const DEPENDEE_TAGS = '@dependee';

    /**
     * @var Lexer
     */
    protected $phpDocLexer;

    /**
     * @var PhpDocParser
     */
    protected $phpDocParser;

    public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }

    public function collectExtraPhpDocs(\ReflectionClass $reflectionClass)
    {
//        $this->resolveInternalTag($reflectionClass);
        $this->resolveDepsInternalTag($reflectionClass);
//        $this->resolveUsesTag($reflectionClass);
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return DepsInternal[]
     */
    public function resolveDepsInternalTag(\ReflectionClass $reflectionClass): array
    {
        $ret = [];

        if ($this->haveTag($reflectionClass, self::DEPS_INTERNAL)) {
            $ret[] = new DepsInternal(
                FQSEN::createClass($reflectionClass->getName()),
                $this->resolve($reflectionClass->getDocComment(), self::DEPS_INTERNAL)
            );
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($this->haveTag($reflectionProperty, self::DEPS_INTERNAL)) {
                $ret[] = new DepsInternal(
                    FQSEN::createProperty($reflectionClass->getName(), $reflectionProperty->getName()),
                    $this->resolve($reflectionProperty->getDocComment(), self::DEPS_INTERNAL)
                );
            }
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($this->haveTag($reflectionMethod, self::DEPS_INTERNAL)) {
                $ret[] = new DepsInternal(
                    FQSEN::createMethod($reflectionClass->getName(), $reflectionMethod->getName()),
                    $this->resolve($reflectionMethod->getDocComment(), self::DEPS_INTERNAL)
                );
            }
        }

        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            if ($this->haveTag($reflectionClassConstant, self::DEPS_INTERNAL)) {
                $ret[] = new DepsInternal(
                    FQSEN::createClassConstant($reflectionClass->getName(), $reflectionClassConstant->getName()),
                    $this->resolve($reflectionClassConstant->getDocComment(), self::DEPS_INTERNAL)
                );
            }
        }

        return $ret;
    }

    protected function resolveInternalTag(\ReflectionClass $reflectionClass)
    {
        $phpDocs = [];

        $phpDocs[] = $reflectionClass->getDocComment();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $phpDocs[] = $reflectionProperty->getDocComment();
        }
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $phpDocs[] = $reflectionMethod->getDocComment();
        }
        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            $phpDocs[] = $reflectionClassConstant->getDocComment();
        }


    }


    protected function resolveUsesTag(\ReflectionClass $reflectionClass)
    {
        $reflectionClass->getDocComment();
    }

    public function resolveCanOnlyUsedByTag(\ReflectionClass $classReflection): array
    {
        if ($phpdoc = $classReflection->getDocComment()) {
            return $this->resolve($phpdoc, self::ONLY_USED_BY_TAGS);
        }

        return [];
//        $ret = [];
//        if ($phpdoc = $classReflection->getNativeReflection()->getDocComment()) {
//            $ret['class_must_have_special_chars_of_method'] = $this->resolve($phpdoc, self::ONLY_USED_BY_TAGS);
//        }
//
//        foreach ($classReflection->getNativeReflection()->getMethods() as $reflectionMethod) {
//            $phpdoc = $reflectionMethod->getDocComment();  // /**\n  * Hogefuga
//            $ret[$reflectionMethod->getName()] = $this->resolve($phpdoc, self::ONLY_USED_BY_TAGS);
//        }
//
//        return [];
    }

    /**
     * @param ClassReflection $classReflection
     * @return string[]
     */
    public function resolveDependerTag(ClassReflection $classReflection): array
    {
        if ($phpdoc = $classReflection->getNativeReflection()->getDocComment()) {
            return $this->resolve($phpdoc, self::DEPENDER_TAGS);
        }

        return [];
    }

    /**
     * @param ClassReflection $classReflection
     * @return string[]
     */
    public function resolveDependeeTag(ClassReflection $classReflection): array
    {
        if ($phpdoc = $classReflection->getNativeReflection()->getDocComment()) {
            return $this->resolve($phpdoc, self::DEPENDEE_TAGS);
        }

        return [];
    }

    protected function haveTag(\Reflector $reflector, string $tag): bool
    {
        if (!method_exists($reflector, 'getDocComment') && $reflector->getDocComment() !== false) {
            return false;
        } elseif (($docComment = $reflector->getDocComment()) === false) {
            return false;
    }

        $tokens = new TokenIterator($this->phpDocLexer->tokenize($reflector->getDocComment()));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        foreach ($phpDocNode->getTagsByName($tag) as $phpDocTagNode) {
            /** @var PhpDocTagNode $phpDocTagNode */
            if (preg_match('/^' . $tag . '/', $phpDocTagNode->__toString()) === 1) {
                return true;
            }
        }

        return false;
    }

    protected function resolve(string $phpdoc, string $tag): array
    {
        $tokens = new TokenIterator($this->phpDocLexer->tokenize($phpdoc));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        $ret = [];
        foreach ($phpDocNode->getTagsByName($tag) as $phpDocTagNode) {
            /** @var PhpDocTagNode $phpDocTagNode */
            if (preg_match('/^' . $tag . '\s+(.+)$/', $phpDocTagNode->__toString(), $matches) === 1) {
                $ret[] = $matches[1];
            }
        };

        return $ret;
    }
}
