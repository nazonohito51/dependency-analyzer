<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use DependencyAnalyzer\Patterns\QualifiedNamePattern;
use Fhaculty\Graph\Vertex;

class DependencyRule
{
    // group namespaces
    // check dependency direction

    /**
     * @var array
     */
    protected $definition;

    /**
     * @var Component[]
     */
    protected $components = [];

    public function __construct(array $definition)
    {
        $this->definition = $definition;

        foreach ($definition as $componentName => $componentDefinition) {
            $definePattern = new QualifiedNamePattern($componentDefinition['define']);

            $dependerPattern = [];
            foreach ($componentDefinition['white'] ?? [] as $item) {
                $dependerPattern[] = new QualifiedNamePattern($definition[$item]['define']);
            }
            foreach ($componentDefinition['black'] ?? [] as $item) {
                $dependerPattern[] = new QualifiedNamePattern(array_map(function (string $pattern) {
                    return '!' . $pattern;
                }, $definition[$item]['define']));
            }
            $this->components[] = new Component($componentName, $definePattern, $dependerPattern);
        }
    }

    public function isSatisfyBy(DependencyGraph $graph): array
    {
        $errors = [];

        foreach ($graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();

            if (is_null($this->getGroupName($depender)) || is_null($this->getGroupName($dependee))) {
                continue;
            }

            foreach ($this->components as $component) {
                if ($component->isBelongedTo($dependee->getId())) {
                    if (!$component->verifyDepender($depender->getId())) {
                        $errors[] = "{$depender->getId()}({$this->getGroupName($depender)}) must not depend on {$dependee->getId()}({$this->getGroupName($dependee)}).";
                    }
                }
            }

//            if (!$this->isValidVertex($depender, $dependee)) {
//                $errors[] = "{$depender->getId()}({$this->getGroupName($depender)}) must not depend on {$dependee->getId()}({$this->getGroupName($dependee)}).";
//            }
        }

        return $errors;
    }

    protected function getGroupName(Vertex $vertex): ?string
    {
        foreach ($this->components as $component) {
            if ($component->isBelongedTo($vertex->getId())) {
                return $component->getName();
            }
        }
//        // TODO: add exclude pattern
//        foreach ($this->definition as $groupName => $groupDefinition) {
//            if (!$this->matchDefine($vertex, $groupDefinition['define'])) {
//                continue;
//            }
//
//            return $groupName;
//        }

        return null;
    }

    protected function matchDefine(Vertex $vertex, array $defines)
    {
        foreach ($defines as $define) {
            $pattern = '/^' . preg_quote($define, '/') . '/';
            if (preg_match($pattern, $vertex->getId()) !== 1) {
                return false;
            }
        }

        return true;
    }

    protected function isValidVertex(Vertex $depender, Vertex $dependee): bool
    {
        $dependerGroup = $this->getGroupName($depender);
        $dependeeGroup = $this->getGroupName($dependee);

        if (is_null($dependerGroup) || is_null($dependeeGroup)) {
            return true;
        } elseif ($dependerGroup === $dependeeGroup) {
            return true;
        } elseif (
            isset($this->definition[$dependeeGroup]['excludeAnalysis']) &&
            in_array($depender->getId(), $this->definition[$dependeeGroup]['excludeAnalysis'])
        ) {
            return true;
        }

        if (isset($this->definition[$dependeeGroup]['white']) && !in_array($dependerGroup, $this->definition[$dependeeGroup]['white'])) {
            return false;
        }

        if (isset($this->definition[$dependeeGroup]['black']) && in_array($dependerGroup, $this->definition[$dependeeGroup]['black'])) {
            return false;
        }

        return true;
    }

    public function getDefinition()
    {
        return $this->definition;
    }
}
