<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraphBuilder;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags\DepsInternal;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\ClassReflection;

class ExtraPhpDocTagResolver
{
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

    /**
     * @var ObserverInterface
     */
    protected $observer = null;

    public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }

    public function setObserver(ObserverInterface $observer): void
    {
        $this->observer = $observer;
    }

    protected function notifyError(string $file, string $fqsen, InvalidFullyQualifiedStructureElementNameException $exception)
    {
        if (!is_null($this->observer)) {
            $this->observer->notifyResolvePhpDocError($file, $fqsen, $exception);
        }
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

        if ($this->haveTag($reflectionClass, DepsInternal::TAG_NAME)) {
            try {
                $ret[] = new DepsInternal(
                    FQSEN::createClass($reflectionClass->getName()),
                    $this->resolve($reflectionClass->getDocComment(), DepsInternal::TAG_NAME)
                );
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
            }
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($this->haveTag($reflectionProperty, DepsInternal::TAG_NAME)) {
                try {
                    $ret[] = new DepsInternal(
                        FQSEN::createProperty($reflectionClass->getName(), $reflectionProperty->getName()),
                        $this->resolve($reflectionProperty->getDocComment(), DepsInternal::TAG_NAME)
                    );
                } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                    $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
                }
            }
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($this->haveTag($reflectionMethod, DepsInternal::TAG_NAME)) {
                try {
                    $ret[] = new DepsInternal(
                        FQSEN::createMethod($reflectionClass->getName(), $reflectionMethod->getName()),
                        $this->resolve($reflectionMethod->getDocComment(), DepsInternal::TAG_NAME)
                    );
                } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                    $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
                }
            }
        }

        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            if ($this->haveTag($reflectionClassConstant, DepsInternal::TAG_NAME)) {
                try {
                    $ret[] = new DepsInternal(
                        FQSEN::createClassConstant($reflectionClass->getName(), $reflectionClassConstant->getName()),
                        $this->resolve($reflectionClassConstant->getDocComment(), DepsInternal::TAG_NAME)
                    );
                } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                    $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
                }
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

    /**
     * @param \ReflectionClass $reflectionClass
     * @return DepsInternal[]
     */
    public function resolveCanOnlyUsedByTag(\ReflectionClass $reflectionClass): array
    {
        $ret = [];
        if ($this->haveTag($reflectionClass, self::ONLY_USED_BY_TAGS)) {
            try {
                $ret[] = new DepsInternal(
                    FQSEN::createClass($reflectionClass->getName()),
                    $this->resolve($reflectionClass->getDocComment(), self::ONLY_USED_BY_TAGS)
                );
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
            }
        }

        return $ret;
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
