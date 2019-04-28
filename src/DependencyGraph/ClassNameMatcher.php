<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName as FQSEN;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;
use DependencyAnalyzer\Exceptions\LogicException;

class ClassNameMatcher
{
    const PHP_NATIVE_CLASSES = '@php_native';
    protected static $nativeClasses = [];

    /**
     * @var FQSEN\Base[]
     */
    protected $patterns = [];

    /**
     * @var FQSEN\Base[]
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

    public function addExcludePatterns(array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (!$this->verifyPattern($pattern)) {
                throw new InvalidQualifiedNamePatternException($pattern);
            }

            $this->excludePatterns[] = $this->formatPattern($pattern);
        }

        return $this;
    }

    protected function verifyPattern(string $pattern)
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

        return FQSEN::createFromString($pattern);
//        $pattern = substr($pattern, 1);
//        $explodedTokens = $this->explodeName($pattern);
//        $endToken = $explodedTokens[count($explodedTokens) - 1];
//
//        if ($endToken === '') {
//            return $pattern . '*';
//        }
//
//        return $pattern;
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

        foreach ($this->excludePatterns as $excludePattern) {
//            if ($excludePattern->include($target)) {
//                return false;
//            }
            if ($this->classNameBelongToPattern($target, $excludePattern)) {
                return false;
            }
        }

        if (count($this->patterns) === 0) {
            return true;
        } else {
            foreach ($this->patterns as $pattern) {
//                if ($pattern->include($target)) {
//                    return true;
//                }
                if ($this->classNameBelongToPattern($target, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function classNameBelongToPattern(Base $target, $pattern): bool
    {
        if (is_string($pattern) && $this->isMagicWordPattern($pattern)) {
            return in_array(substr($target->toString(), 1), self::$nativeClasses);
        } elseif (!$pattern instanceof FQSEN\Base) {
            throw new LogicException('Class name matching is failed, because compare with invalid pattern object.');
        }

        return $pattern->include($target);

//        $explodedClassName = $this->explodeName($className);
//        $explodedPattern = $this->explodeName($pattern);
//
//        if (count($explodedPattern) === 1 && $explodedPattern[0] === '*') {
//            // Pattern likely '\\' will match with all className.
//            return true;
//        }
//        if (count($explodedClassName) < count($explodedPattern)) {
//            return false;
//        }
//
//        foreach ($explodedClassName as $index => $pattern) {
//            if (!isset($explodedPattern[$index])) {
//                return false;
//            } elseif ($explodedPattern[$index] === '*') {
//                return true;
//            } elseif ($explodedPattern[$index] !== $pattern) {
//                return false;
//            }
//        }
//
//        return true;
    }

    protected function explodeName(string $qualifiedName)
    {
        return explode('\\', $qualifiedName);
    }

    public static function setPhpNativeClasses(array $nativeClasses)
    {
        self::$nativeClasses = $nativeClasses;
    }

    public function toArray()
    {
        return [
            'include' => array_map(function (Base $fqsen) {
                return $fqsen->toString();
            }, $this->patterns),
            'exclude' => array_map(function (Base $fqsen) {
                return $fqsen->toString();
            }, $this->excludePatterns)
        ];
    }
}
