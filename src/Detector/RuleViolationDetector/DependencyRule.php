<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Detector\RuleViolationDetector;

use DependencyAnalyzer\DependencyGraph;
use Fhaculty\Graph\Vertex;

class DependencyRule
{
    // group namespaces
    // check dependency direction

    /**
     * @var array
     */
    protected $definition;

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    public function isSatisfyBy(DependencyGraph $graph): array
    {
        $errors = [];

        foreach ($graph->getDependencyArrows() as $edge) {
            $depender = $edge->getVertexStart();
            $dependee = $edge->getVertexEnd();
            if (!$this->isValidVertex($depender, $dependee)) {
                $errors[] = "{$depender->getId()}({$this->getGroupName($depender)}) must not depend on {$dependee->getId()}({$this->getGroupName($dependee)}).";;
            }
        }

        return $errors;
    }

    protected function getGroupName(Vertex $vertex): ?string
    {
        // TODO: add exclude pattern
        foreach ($this->definition as $groupName => $groupDefinition) {
            if (!$this->matchDefine($vertex, $groupDefinition['define'])) {
                continue;
            }

            return $groupName;
        }

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
