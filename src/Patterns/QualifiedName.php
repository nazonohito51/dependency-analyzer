<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Patterns;

use DependencyAnalyzer\Exceptions\InvalidQualifiedNameException;
use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;

class QualifiedName
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
        return preg_match('/^!?' . preg_quote('\\', '/') . '(.*)$/', $pattern, $matches) === 1 &&
            strlen($matches[1]) > 0;
    }

    protected function addPattern(string $pattern)
    {
        if ($this->isExcludePattern($pattern)) {
            $this->excludePatterns[] = substr($pattern, 2);
        } else {
            $this->patterns[] = substr($pattern, 1);
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

        foreach ($this->patterns as $pattern) {
            if ($this->classNameBelongToPattern($className, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function classNameBelongToPattern(string $className, string $pattern)
    {
        $separatedClassName = $this->explodeName($className);
        $separatedPattern = $this->explodeName($pattern);

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
        $separatedNames = explode('\\', $qualifiedName);

        if ($separatedNames === false || empty($separatedNames[0])) {
            throw new InvalidQualifiedNameException($qualifiedName);
        }

        return $separatedNames;
    }
}
