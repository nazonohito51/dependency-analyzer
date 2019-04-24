<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

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

    public static function createClass(string $className): self
    {
        return new self($className, self::TYPE_CLASS);
    }

    public static function createMethod(string $className, string $functionName)
    {
        return new self("{$className}::{$functionName}()", self::TYPE_METHOD);
    }

    public static function createProperty(string $className, string $propertyName)
    {
        return new self("{$className}::\${$propertyName}", self::TYPE_PROPERTY);
    }

    public static function createClassConstant(string $className, string $constantName)
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

    public static function createFunction(string $functionName)
    {
        return new self("{$functionName}()", self::TYPE_FUNCTION);
    }

    public static function createConstant(string $constantName)
    {
        return new self("{$constantName}", self::TYPE_CONSTANT);
    }

    public function isClass()
    {
        return $this->type === self::TYPE_CLASS;
    }

    public function isMethod()
    {
        return $this->type === self::TYPE_METHOD;
    }

    public function isProperty()
    {
        return $this->type === self::TYPE_PROPERTY;
    }

    public function isClassConstant()
    {
        return $this->type === self::TYPE_CLASS_CONSTANT;
    }

    public function isInterface()
    {
        return $this->type === self::TYPE_INTERFACE;
    }

    public function isTrait()
    {
        return $this->type === self::TYPE_TRAIT;
    }

    public function isFunction()
    {
        return $this->type === self::TYPE_FUNCTION;
    }

    public function isConstant()
    {
        return $this->type === self::TYPE_CONSTANT;
    }

    public function toString()
    {
        return $this->elementName;
    }
}
