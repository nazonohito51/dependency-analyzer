<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraphBuilder;

use DependencyAnalyzer\DependencyDumper;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags\DepsInternal;
use DependencyAnalyzer\DependencyGraph\ExtraPhpDocTags\Internal;
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

    /**
     * @param \ReflectionClass $reflectionClass
     * @return DepsInternal[]
     */
    public function resolveDepsInternalTag(\ReflectionClass $reflectionClass): array
    {
        $ret = [];

        foreach ($this->collectReflectionsHavingTag($reflectionClass, DepsInternal::getTagName()) as $reflection) {
            try {
                $ret[] = new DepsInternal(
                    FQSEN::createFromReflection($reflection),
                    $this->resolve($reflection->getDocComment(), DepsInternal::getTagName())
                );
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
            }
        }

        return $ret;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return Internal[]
     */
    public function resolveInternalTag(\ReflectionClass $reflectionClass): array
    {
        $ret = [];
        $package = $this->resolvePackageTag($reflectionClass) ??
            FQSEN::createFromReflection($reflectionClass)->getFullyQualifiedNamespaceName()->toString();

        foreach ($this->collectReflectionsHavingTag($reflectionClass, Internal::getTagName()) as $reflection) {
            try {
                $ret[] = new Internal(FQSEN::createFromReflection($reflection), $package);
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
            }
        }

        return $ret;
    }

    public function resolvePackageTag(\ReflectionClass $reflectionClass): ?string
    {
        if ($this->haveTag($reflectionClass, '@package')) {
            try {
                $ret[] = new DepsInternal(
                    FQSEN::createClass($reflectionClass->getName()),
                    $this->resolve($reflectionClass->getDocComment(), '@package')
                );
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                $this->notifyError($reflectionClass->getFileName(), $reflectionClass->getName(), $e);
            }
        }

        return null;
    }

    protected function collectReflectionsHavingTag(\ReflectionClass $reflectionClass, string $tagName): array
    {
        $ret = [];

        if ($this->haveTag($reflectionClass, $tagName)) {
            $ret[] = $reflectionClass;
        }
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($this->haveTag($reflectionProperty, $tagName)) {
                $ret[] = $reflectionProperty;
            }
        }
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($this->haveTag($reflectionMethod, DepsInternal::getTagName())) {
                $ret[] = $reflectionMethod;
            }
        }
        foreach ($reflectionClass->getReflectionConstants() as $reflectionClassConstant) {
            if ($this->haveTag($reflectionClassConstant, DepsInternal::getTagName())) {
                $ret[] = $reflectionClassConstant;
            }
        }

        return $ret;
    }


//    protected function resolveUsesTag(\ReflectionClass $reflectionClass)
//    {
//        $reflectionClass->getDocComment();
//    }

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
