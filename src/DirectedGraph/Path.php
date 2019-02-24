<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DirectedGraph;

use DependencyAnalyzer\Exceptions\InvalidEdgeOnPathException;
use DependencyAnalyzer\Exceptions\LogicException;
use Fhaculty\Graph\Edge\Directed;

class Path implements \Countable
{
    /**
     * @var Directed[]
     */
    private $edges;

    public function __construct(array $edges = [])
    {
        foreach ($edges as $edge) {
            if (!$edge instanceof Directed) {
                throw new LogicException();
            }
        }
        $this->edges = $edges;
    }

    public function addEdge(Directed $edge)
    {
        $last = end($this->edges);

        if ($last !== false && $last->getVertexEnd()->getId() !== $edge->getVertexStart()->getId()) {
            throw new InvalidEdgeOnPathException($edge);
        }
        $edges = $this->edges;
        $edges[] = $edge;
        return new self($edges);
    }

    public function haveCycle(): bool
    {
        $visitedVertex = [];

        foreach ($this->edges as $edge) {
            $visitedVertex[] = $edge->getVertexStart()->getId();

            if (in_array($edge->getVertexEnd()->getId(), $visitedVertex)) {
                return true;
            }
        }

        return false;
    }

    public function isSimpleCycle(): bool
    {
        return (
            $this->haveCycle() &&
            ($this->edges[0]->getVertexStart()->getId() === end($this->edges)->getVertexEnd()->getId())
        );
    }

    public function getIds(): array
    {
        if ($this->count() === 0) {
            return [];
        }

        $ids = [$this->edges[0]->getVertexStart()->getId()];
        foreach ($this->edges as $edge) {
            if (!in_array($edge->getVertexEnd()->getId(), $ids)) {
                $ids[] = $edge->getVertexEnd()->getId();
            }
        }

        return $ids;
    }

    public function isEqual(Path $that): bool
    {
        if ($this->count() !== $that->count()) {
            return false;
        } elseif ($this->haveCycle() !== $that->haveCycle()) {
            return false;
        } elseif ($this->isSimpleCycle() !== $that->isSimpleCycle()) {
            return false;
        } elseif ($this->isSimpleCycle() && $that->isSimpleCycle()) {
            return empty(array_diff($this->getIds(), $that->getIds())) && empty(array_diff($that->getIds(), $this->getIds()));
        } else {
            return $this->getIds() === $that->getIds();
        }
    }

    public function count()
    {
        return count($this->edges);
    }
}
