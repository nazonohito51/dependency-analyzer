<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraphBuilder;

/**
 * @canOnlyUsedBy \DependencyAnalyzer\DependencyGraphBuilder
 */
class UnknownReflectionClass extends \ReflectionClass
{
    protected $unknownClassName;

    public function __construct(string $name)
    {
        try {
            parent::__construct('');
        } catch (\ReflectionException $e) {
            //
        }

        $this->unknownClassName = $name;
    }

    public function getName()
    {
        return $this->unknownClassName;
    }
}
