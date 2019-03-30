<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Patterns;

use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;

class QualifiedNamePattern
{
    const PHP_NATIVE_CLASSES = '@php_native';
    protected static $nativeClasses = [];

    protected $patterns = [];
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

    protected function verifyPattern(string $pattern)
    {
        return (
            preg_match('/^!?' . preg_quote('\\', '/') . '([^\*]*)\*?$/', $pattern, $matches) === 1 ||
            $this->isMagicWordPattern($pattern)
        );
    }

    protected function addPattern(string $pattern)
    {
        if ($this->isExcludePattern($pattern)) {
            $this->excludePatterns[] = $this->formatPattern(substr($pattern, 1));
        } else {
            $this->patterns[] = $this->formatPattern($pattern);
        }
    }

    protected function formatPattern(string $pattern)
    {
        if ($this->isMagicWordPattern($pattern)) {
            return $pattern;
        }

        $pattern = substr($pattern, 1);
        $explodedTokens = $this->explodeName($pattern);
        $endToken = $explodedTokens[count($explodedTokens) - 1];

        if ($endToken === '') {
            return $pattern . '*';
        }

        return $pattern;
    }

    protected function isExcludePattern(string $pattern)
    {
        return preg_match('/^!/', $pattern) === 1;
    }

    protected function isMagicWordPattern(string $pattern)
    {
        return in_array($pattern, [
            self::PHP_NATIVE_CLASSES,
            '!' . self::PHP_NATIVE_CLASSES,
        ]);
    }

    public function isMatch(string $className)
    {
        foreach ($this->excludePatterns as $excludePattern) {
            if ($this->classNameBelongToPattern($className, $excludePattern)) {
                return false;
            }
        }

        if (count($this->patterns) === 0) {
            return true;
        } else {
            foreach ($this->patterns as $pattern) {
                if ($this->classNameBelongToPattern($className, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function classNameBelongToPattern(string $className, string $pattern)
    {
        if ($this->isMagicWordPattern($pattern)) {
            return in_array($className, self::$nativeClasses);
        }

        $explodedClassName = $this->explodeName($className);
        $explodedPattern = $this->explodeName($pattern);

        if (count($explodedPattern) === 1 && $explodedPattern[0] === '*') {
            // Pattern likely '\\' will match with all className.
            return true;
        }
        if (count($explodedClassName) < count($explodedPattern)) {
            return false;
        }

        foreach ($explodedClassName as $index => $pattern) {
            if (!isset($explodedPattern[$index])) {
                return false;
            } elseif ($explodedPattern[$index] === '*') {
                return true;
            } elseif ($explodedPattern[$index] !== $pattern) {
                return false;
            }
        }

        return true;
    }

    protected function explodeName(string $qualifiedName)
    {
        return explode('\\', $qualifiedName);
    }

    public static function setPhpNativeClasses(array $nativeClasses)
    {
        self::$nativeClasses = $nativeClasses;
    }
}
