<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

use Fhaculty\Graph\Edge\Directed;

class InvalidEdgeOnPathException extends RuntimeException
{
    /**
     * @var Directed
     */
    private $edge;

    public function __construct(Directed $edge, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->edge = $edge;
    }
}
