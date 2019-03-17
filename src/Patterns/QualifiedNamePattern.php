<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Patterns;

use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;

class QualifiedNamePattern
{
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
        return preg_match('/^!?' . preg_quote('\\', '/') . '(.*)$/', $pattern, $matches) === 1;
    }

    protected function addPattern(string $pattern)
    {
        if ($this->isExcludePattern($pattern)) {
            $this->excludePatterns[] = rtrim(substr($pattern, 2), '\\');
        } else {
            $this->patterns[] = rtrim(substr($pattern, 1), '\\');
        }
    }

    protected function isExcludePattern(string $pattern)
    {
        return preg_match('/^!/', $pattern) === 1;
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
}
