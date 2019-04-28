<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Base;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Class_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\ClassConstant;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Constant;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Function_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Interface_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Method;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Namespace_;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Property;
use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName\Trait_;
use DependencyAnalyzer\Exceptions\InvalidFullyQualifiedStructureElementNameException;

class FullyQualifiedStructuralElementName
{
    const TYPE_NAMESPACE = 'namespace';
    const TYPE_CLASS = 'class';
    const TYPE_METHOD = 'method';
    const TYPE_PROPERTY = 'property';
    const TYPE_CLASS_CONSTANT = 'class_constant';
    const TYPE_INTERFACE = 'interface';
    const TYPE_TRAIT = 'trait';
    const TYPE_FUNCTION = 'function';
    const TYPE_CONSTANT = 'constant';

    public static function createFromString(string $element): Base
    {
        if (self::isNamespaceElement($element)) {
            return new Namespace_($element);
        } elseif (self::isMethodElement($element)) {
            return new Method($element);
        } elseif (self::isPropertyElement($element)) {
            return new Property($element);
        } elseif (self::isClassConstantElement($element)) {
            return new ClassConstant($element);
        } elseif (self::isFunctionElement($element)) {
            return new Function_($element);
        } elseif (self::isFullyQualifiedClassElement($element)) {
            return new Class_($element);
        }

        throw new InvalidFullyQualifiedStructureElementNameException($element);
    }

    protected static function isNamespaceElement(string $element): bool
    {
        if ($element === '\\') {
            return true;
        }

        // TODO: This is my original definition...
        return substr($element, -1) === '\\' &&
            self::isFullyQualifiedClassElement(substr($element, 0, -1));
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

    public static function createNamespace(string $namespaceName): Base
    {
        return new Namespace_($namespaceName);
    }

    public static function createClass(string $className): Base
    {
        return new Class_($className);
    }

    public static function createMethod(string $className, string $functionName): Base
    {
        return new Method("{$className}::{$functionName}()");
    }

    public static function createProperty(string $className, string $propertyName): Base
    {
        return new Property("{$className}::\${$propertyName}");
    }

    public static function createClassConstant(string $className, string $constantName): Base
    {
        return new ClassConstant("{$className}::{$constantName}");
    }

    public static function createInterface(string $interfaceName): Base
    {
        return new Interface_($interfaceName);
    }

    public static function createTrait(string $traitName): Base
    {
        return new Trait_($traitName);
    }

    public static function createFunction(string $functionName): Base
    {
        return new Function_("{$functionName}()");
    }

    public static function createConstant(string $constantName): Base
    {
        return new Constant("{$constantName}");
    }
}
