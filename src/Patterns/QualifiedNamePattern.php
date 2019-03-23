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
            preg_match('/^!?' . preg_quote('\\', '/') . '(.*)$/', $pattern, $matches) === 1 ||
            $this->isMagicWordPattern($pattern)
        );
    }

    protected function addPattern(string $pattern)
    {
        if ($this->isExcludePattern($pattern)) {
            $this->excludePatterns[] = $this->isMagicWordPattern($pattern) ? substr($pattern, 1) : rtrim(substr($pattern, 2), '\\');
        } else {
            $this->patterns[] = $this->isMagicWordPattern($pattern) ? $pattern : rtrim(substr($pattern, 1), '\\');
        }
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

        $separatedClassName = $this->explodeName($className);
        $separatedPattern = $this->explodeName($pattern);

        if (count($separatedPattern) === 1 && $separatedPattern[0] === '') {
            // Pattern likely '\\' will match with all className.
            return true;
        }
        if (count($separatedClassName) < count($separatedPattern)) {
            return false;
        }

        foreach ($separatedPattern as $index => $pattern) {
            if ($separatedClassName[$index] !== $pattern) {
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
