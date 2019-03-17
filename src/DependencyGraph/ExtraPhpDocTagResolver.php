<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

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

    public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
    }

    public function resolveCanOnlyUsedByTag(ClassReflection $classReflection): array
    {
        if ($phpdoc = $classReflection->getNativeReflection()->getDocComment()) {
            return $this->resolve($phpdoc, self::ONLY_USED_BY_TAGS);
        }

        return [];
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

    protected function resolve(string $phpdoc, string $tag): array
    {
        $tokens = new TokenIterator($this->phpDocLexer->tokenize($phpdoc));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        $ret = [];
        foreach ($phpDocNode->getTagsByName($tag) as $phpDocTagNode) {
            /** @var PhpDocTagNode $phpDocTagNode */
            preg_match('/^' . $tag . '\s+(.+)$/', $phpDocTagNode->__toString(), $matches);
            $ret[] = $matches[1];
        };

        return $ret;
    }
}
