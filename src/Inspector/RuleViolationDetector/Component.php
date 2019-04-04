<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\Matcher\ClassNameMatcher;

class Component
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ClassNameMatcher
     */
    protected $pattern;

    /**
     * @var array|ClassNameMatcher[]
     */
    protected $dependerPatterns;

    /**
     * @var array|ClassNameMatcher[]
     */
    protected $dependeePatterns;

    /**
     * Component constructor.
     * @param string $name
     * @param ClassNameMatcher $pattern
     * @param ClassNameMatcher $dependerPatterns
     * @param ClassNameMatcher $dependeePatterns
     */
    public function __construct(string $name, ClassNameMatcher $pattern, ClassNameMatcher $dependerPatterns = null, ClassNameMatcher $dependeePatterns = null)
    {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->dependerPatterns = $dependerPatterns;
        $this->dependeePatterns = $dependeePatterns;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isBelongedTo(string $className): bool
    {
        return $this->pattern->isMatch($className);
    }

    public function verifyDepender(string $className): bool
    {
        if ($this->isBelongedTo($className)) {
            return true;
        } elseif (is_null($this->dependerPatterns)) {
            return true;
        }

        return $this->dependerPatterns->isMatch($className);
//        return $this->checkPatterns($className, $this->dependerPatterns);
    }

    public function verifyDependee(string $className): bool
    {
        if ($this->isBelongedTo($className)) {
            return true;
        } elseif (is_null($this->dependeePatterns)) {
            return true;
        }

        return $this->dependeePatterns->isMatch($className);
//        return $this->checkPatterns($className, $this->dependeePatterns);
    }

    /**
     * @param string $className
     * @param ClassNameMatcher[] $patterns
     * @return bool
     */
    protected function checkPatterns(string $className, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern->isMatch($className)) {
                return true;
            }
        }

        return false;
    }
}
