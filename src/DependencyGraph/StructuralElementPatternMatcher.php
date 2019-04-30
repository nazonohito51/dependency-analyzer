<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\DependencyTypes\Base;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher\FQSENMatcher;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher\Matchable;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher\PhpNativeClassesMatcher;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;

class StructuralElementPatternMatcher
{
    const PHP_NATIVE_CLASSES = '@php_native';
    protected static $nativeClasses = [];

    /**
     * @var Matchable[]
     */
    protected $patterns = [];

    /**
     * @var Matchable[]
     */
    protected $excludePatterns = [];

    public function __construct(array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (!$this->verifyPattern($pattern)) {
                throw new InvalidQualifiedNamePatternException($pattern);
            }

            $this->addPattern($pattern);
        }
    }

    public function addExcludePatterns(array $patterns): self
    {
        foreach ($patterns as $pattern) {
            if (!$this->verifyPattern($pattern)) {
                throw new InvalidQualifiedNamePatternException($pattern);
            }

            $this->excludePatterns[] = $this->createPattern($pattern);
        }

        return $this;
    }

    protected function verifyPattern(string $pattern): bool
    {
        if ($this->isMagicWordPattern($pattern)) {
            return true;
        }

        try {
            $this->isExcludePattern($pattern) ? FQSEN::createFromString(substr($pattern, 1)) : FQSEN::createFromString($pattern);
        } catch (InvalidFullyQualifiedStructureElementNameException $e) {
            return false;
        }

        return true;
    }

    protected function addPattern(string $pattern): void
    {
        if ($this->isExcludePattern($pattern)) {
            $this->excludePatterns[] = $this->createPattern(substr($pattern, 1));
        } else {
            $this->patterns[] = $this->createPattern($pattern);
        }
    }

    protected function createPattern(string $pattern): Matchable
    {
        if ($this->isMagicWordPattern($pattern)) {
            return new PhpNativeClassesMatcher(self::$nativeClasses);
        }

        return new FQSENMatcher(FQSEN::createFromString($pattern));
    }

    protected function isExcludePattern(string $pattern): bool
    {
        return preg_match('/^!/', $pattern) === 1;
    }

    protected function isMagicWordPattern(string $pattern): bool
    {
        return in_array($pattern, [
            self::PHP_NATIVE_CLASSES,
            '!' . self::PHP_NATIVE_CLASSES,
        ]);
    }

    public function isMatch(string $className): bool
    {
        try {
            if (!substr($className, 0, 1) !== '\\') {
                $className = '\\' . $className;
            }

            $target = FQSEN::createFromString($className);
        } catch (InvalidFullyQualifiedStructureElementNameException $e) {
            return false;
        }

        return $this->isMatchWithFQSEN($target);
    }

    public function isMatchWithFQSEN(FQSEN\Base $target): bool
    {
        foreach ($this->excludePatterns as $excludePattern) {
            if ($excludePattern->isMatch($target)) {
                return false;
            }
        }

        if (count($this->patterns) === 0) {
            return true;
        } else {
            foreach ($this->patterns as $pattern) {
                if ($pattern->isMatch($target)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function setPhpNativeClasses(array $nativeClasses): void
    {
        self::$nativeClasses = array_map(function (string $className) {
            return substr($className, 0, 1) !== '\\' ? "\\{$className}" : $className;
        }, $nativeClasses);
    }

    public function toArray(): array
    {
        return [
            'include' => array_map(function (Matchable $fqsen) {
                return $fqsen->getPattern();
            }, $this->patterns),
            'exclude' => array_map(function (Matchable $fqsen) {
                return $fqsen->getPattern();
            }, $this->excludePatterns)
        ];
    }
}
