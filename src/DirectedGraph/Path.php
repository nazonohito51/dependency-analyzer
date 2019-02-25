<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DirectedGraph;

use DependencyAnalyzer\Exceptions\InvalidEdgeOnPathException;
use Fhaculty\Graph\Edge\Directed;

class Path implements \Countable
{
    /**
     * @var Directed[]
     */
    private $edges = [];

    public function __construct(array $edges = [])
    {
        foreach ($edges as $edge) {
            if (!$this->isCanConnectTo($edge)) {
                throw new InvalidEdgeOnPathException($edge);
            }

            $this->edges[] = $edge;
        }
    }

    protected function isCanConnectTo(Directed $edge): bool
    {
        if ($last = $this->getLastEdge()) {
            return $last->getVertexEnd()->getId() === $edge->getVertexStart()->getId();
        }

        return true;
    }

    public function addEdge(Directed $edge): self
    {
        if (!$this->isCanConnectTo($edge)) {
            throw new InvalidEdgeOnPathException($edge);
        }

        return new self(array_merge($this->edges, [$edge]));
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
        if (!$this->haveCycle()) {
            return false;
        }

        $visitedVertices = [];
        foreach ($this->edges as $edge) {
            if (in_array($edge->getVertexStart()->getId(), $visitedVertices)) {
                return false;
            }
            $visitedVertices[] = $edge->getVertexStart()->getId();
        }

        return $this->getFirstEdge()->getVertexStart()->getId() === $this->getLastEdge()->getVertexEnd()->getId();
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
        if ($this->count() === 0 || $that->count() === 0) {
            // Path do not have edge is not equal to anything.
            return false;
        } elseif ($this->count() !== $that->count()) {
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

    protected function getFirstEdge(): ?Directed
    {
        if ($this->count() === 0) {
            return null;
        }

        return $this->edges[0];
    }

    protected function getLastEdge(): ?Directed
    {
        if ($this->count() === 0) {
            return null;
        }

        $edge = end($this->edges);
        reset($this->edges);

        return $edge;
    }

    public function count(): int
    {
        return count($this->edges);
    }
}
