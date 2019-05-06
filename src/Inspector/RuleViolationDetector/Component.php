<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Inspector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base as FQSEN;
use DependencyAnalyzer\DependencyGraph\StructuralElementPatternMatcher;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;
use DependencyAnalyzer\Exceptions\InvalidQualifiedNamePatternException;

class Component
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var StructuralElementPatternMatcher
     */
    protected $matcher;

    /**
     * @var StructuralElementPatternMatcher
     */
    protected $publicMatcher;

    /**
     * @var StructuralElementPatternMatcher
     */
    protected $dependerMatcher;

    /**
     * @var StructuralElementPatternMatcher
     */
    protected $dependeeMatcher;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var StructuralElementPatternMatcher[]
     */
    protected $extraPatterns = [];

    public function __construct(
        string $name,
        StructuralElementPatternMatcher $pattern,
        StructuralElementPatternMatcher $dependerPatterns = null,
        StructuralElementPatternMatcher $dependeePatterns = null,
        StructuralElementPatternMatcher $publicPattern = null,
        array $extraPatterns = null
    ) {
        $this->name = $name;
        $this->matcher = $pattern;
        $this->dependerMatcher = $dependerPatterns;
        $this->dependeeMatcher = $dependeePatterns;
        $this->publicMatcher = $publicPattern;

        if (!is_null($extraPatterns)) {
            try {
                foreach ($extraPatterns as $callee => $callerPattern) {
                    FullyQualifiedStructuralElementName::createFromString($callee);
                }
            } catch (InvalidFullyQualifiedStructureElementNameException $e) {
                throw new InvalidQualifiedNamePatternException($callee);
            }
            $this->extraPatterns = $extraPatterns;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDefineMatcher(): StructuralElementPatternMatcher
    {
        return $this->matcher;
    }

    public function isBelongedTo(string $className): bool
    {
        return $this->matcher->isMatch($className);
    }

    public function verifyDepender(FQSEN $depender, FQSEN $dependee): bool
    {
        if ($this->isBelongedTo($depender->toString())) {
            return true;
        }

        if (!empty($extraPatterns = $this->getExtraPatterns($dependee))) {
            $ret = true;
            foreach ($extraPatterns as $extraPattern) {
                $ret = $ret && $extraPattern->isMatchWithFQSEN($depender);
            }
            return $ret;
        }

        return (
            (is_null($this->publicMatcher) ? true : $this->publicMatcher->isMatchWithFQSEN($dependee)) &&
            (is_null($this->dependerMatcher) ? true : $this->dependerMatcher->isMatchWithFQSEN($depender))
        );
    }

    public function verifyDependee(string $className): bool
    {
        if ($this->isBelongedTo($className)) {
            return true;
        } elseif (is_null($this->dependeeMatcher)) {
            return true;
        }

        return $this->dependeeMatcher->isMatch($className);
    }

    /**
     * @param string $className
     * @param StructuralElementPatternMatcher[] $patterns
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

    /**
     * @param FQSEN $dependee
     * @return StructuralElementPatternMatcher[]
     */
    protected function getExtraPatterns(FQSEN $dependee): array
    {
        $ret = [];
        foreach ($this->extraPatterns as $callee => $callerPattern) {
            $calleeFQSEN = FullyQualifiedStructuralElementName::createFromString($callee);
            if ($calleeFQSEN->include($dependee)) {
                $ret[] = $this->extraPatterns[$callee];
            }
        }

        return $ret;
    }

    public function setAttribute(string $key, $name): void
    {
        $this->attributes[$key] = $name;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function toArray()
    {
        $ret = [
            'define' => $this->matcher->toArray()
        ];

        if (!is_null($this->dependerMatcher)) {
            $ret['depender'] = $this->dependerMatcher->toArray();
        }
        if (!is_null($this->dependeeMatcher)) {
            $ret['dependee'] = $this->dependeeMatcher->toArray();
        }
        if (!is_null($this->publicMatcher)) {
            $ret['public'] = $this->publicMatcher->toArray();
        }
        if (!empty($this->extraPatterns)) {
            $ret['extra'] = [];
            foreach ($this->extraPatterns as $callee => $callerPattern) {
                $ret['extra'][$callee] = $callerPattern->toArray();
            }
        }

        return $ret;
    }
}
