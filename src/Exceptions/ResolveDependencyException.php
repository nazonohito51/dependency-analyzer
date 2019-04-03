<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

use PhpParser\Node;

class ResolveDependencyException extends RuntimeException
{
    /**
     * @var Node
     */
    private $node;

    public function __construct(Node $node, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->node = $node;
    }

    public function getNodeLine()
    {
        return $this->node->getLine();
    }

    public function getNodeType()
    {
        return $this->node->getType();
    }

    public function __toString(): string
    {
        return parent::__toString() . ' ' . $this->nodeToString();
    }

    protected function nodeToString(): string
    {
        return "type: {$this->getNodeType()}, line: {$this->getNodeLine()}";
    }
}
