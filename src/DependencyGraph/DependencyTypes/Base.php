<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph\DependencyTypes;

abstract class Base
{
    public abstract function getType(): string;
    public abstract function isEqual(Base $that): bool;
}
