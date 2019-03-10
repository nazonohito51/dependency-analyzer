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

    public function __toString(): string
    {
        return parent::__toString() . ' ' . $this->nodeToString();
    }

    protected function nodeToString(): string
    {
        return "type: {$this->node->getType()}, line: {$this->node->getLine()}";
    }
}
