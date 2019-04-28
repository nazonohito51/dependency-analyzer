<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

use DependencyAnalyzer\DependencyGraph\FullyQualifiedStructuralElementName;

class Namespace_ extends Base
{
    public function getType(): string
    {
        return FullyQualifiedStructuralElementName::TYPE_NAMESPACE;
    }

    public function include(Base $that): bool
    {
        if ($this->toString() === '\\') {
            // Pattern likely '\\' will match with all className.
            return true;
        } elseif ($this->isSame($that)) {
            return true;
        } elseif (count($this->explode()) > count($that->explode())) {
            return false;
        }

        $namesOfThat = $that->explode();
        foreach ($this->explode() as $index => $name) {
            if (!isset($namesOfThat[$index]) || $namesOfThat[$index] !== $name) {
                return false;
            }
        }

        return true;
    }

    public function isNamespace(): bool
    {
        return true;
    }
}
