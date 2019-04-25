<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

class FullyQualifiedStructuralElementName
{
    const TYPE_CLASS = 'class';
    const TYPE_METHOD = 'method';
    const TYPE_PROPERTY = 'property';
    const TYPE_CLASS_CONSTANT = 'class_constant';
    const TYPE_INTERFACE = 'interface';
    const TYPE_TRAIT = 'trait';
    const TYPE_FUNCTION = 'function';
    const TYPE_CONSTANT = 'constant';

    /**
     * @var string
     */
    private $elementName;

    /**
     * @var string
     */
    private $type;

    protected function __construct(string $elementName, string $type)
    {
        $this->elementName = $elementName;
        $this->type = $type;
    }

    public static function createFromString(string $element): self
    {
        if (self::isFullyQualifiedClassElement($element)) {
            $type = self::TYPE_CLASS;
        } elseif (self::isMethodElement($element)) {
            $type = self::TYPE_METHOD;
        } elseif (self::isPropertyElement($element)) {
            $type = self::TYPE_PROPERTY;
        } elseif (self::isClassConstantElement($element)) {
            $type = self::TYPE_CLASS_CONSTANT;
        } elseif (self::isFunctionElement($element)) {
            $type = self::TYPE_FUNCTION;
        } else {
            throw new InvalidFullyQualifiedStructureElementNameException($element);
        }

        return new self($element, $type);
    }

    protected static function isMethodElement(string $element): bool
    {
        if (strpos($element, '::') === false) {
            return false;
        }

        list($fqcn, $method) = explode('::', $element, 2);

        return self::isFullyQualifiedClassElement($fqcn) &&
            substr($method, -2) === '()' &&
            self::isName(substr($method, 0, -2));
    }

    protected static function isPropertyElement(string $element): bool
    {
        if (strpos($element, '::') === false) {
            return false;
        }

        list($fqcn, $property) = explode('::', $element, 2);

        return self::isFullyQualifiedClassElement($fqcn) &&
            substr($property, 0, 1) === '$' &&
            self::isName(substr($property, 1));
    }

    protected static function isClassConstantElement(string $element): bool
    {
        if (strpos($element, '::') === false) {
            return false;
        }

        list($fqcn, $constant) = explode('::', $element, 2);

        return self::isFullyQualifiedClassElement($fqcn) &&
            self::isName($constant);
    }

    protected static function isFunctionElement(string $element): bool
    {
        return substr($element, -2) === '()' &&
            self::isFullyQualifiedClassElement(substr($element, 0, -2));
    }

    protected static function isFullyQualifiedClassElement(string $element): bool
    {
        if (substr($element, 0, 1) !== '\\') {
            return false;
        }

        $names = explode('\\', $element);
        if (array_shift($names) !== '') {
            return false;
        } elseif (count($names) <= 0) {
            return false;
        }
        foreach ($names as $name) {
            if (!self::isName($name)) {
                return false;
            }
        }

        return true;
    }

    protected static function isName(string $element): bool
    {
        return preg_match('/^[a-zA-Z\_][0-9a-zA-Z\_]+$/', $element) === 1;
    }

    public static function createClass(string $className): self
    {
        return new self($className, self::TYPE_CLASS);
    }

    public static function createMethod(string $className, string $functionName): self
    {
        return new self("{$className}::{$functionName}()", self::TYPE_METHOD);
    }

    public static function createProperty(string $className, string $propertyName): self
    {
        return new self("{$className}::\${$propertyName}", self::TYPE_PROPERTY);
    }

    public static function createClassConstant(string $className, string $constantName): self
    {
        return new self("{$className}::{$constantName}", self::TYPE_CLASS_CONSTANT);
    }

    public static function createInterface(string $interfaceName): self
    {
        return new self($interfaceName, self::TYPE_INTERFACE);
    }

    public static function createTrait(string $traitName): self
    {
        return new self($traitName, self::TYPE_TRAIT);
    }

    public static function createFunction(string $functionName): self
    {
        return new self("{$functionName}()", self::TYPE_FUNCTION);
    }

    public static function createConstant(string $constantName): self
    {
        return new self("{$constantName}", self::TYPE_CONSTANT);
    }

    public function isClass(): bool
    {
        return $this->type === self::TYPE_CLASS;
    }

    public function isMethod(): bool
    {
        return $this->type === self::TYPE_METHOD;
    }

    public function isProperty(): bool
    {
        return $this->type === self::TYPE_PROPERTY;
    }

    public function isClassConstant(): bool
    {
        return $this->type === self::TYPE_CLASS_CONSTANT;
    }

    public function isInterface(): bool
    {
        return $this->type === self::TYPE_INTERFACE;
    }

    public function isTrait(): bool
    {
        return $this->type === self::TYPE_TRAIT;
    }

    public function isFunction(): bool
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    public function isConstant(): bool
    {
        return $this->type === self::TYPE_CONSTANT;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toString(): string
    {
        return $this->elementName;
    }
}
