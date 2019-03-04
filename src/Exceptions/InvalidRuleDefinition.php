<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class InvalidRuleDefinition extends RuntimeException
{
    /**
     * @var array
     */
    private $ruleDefinition;

    public function __construct(array $ruleDefinition, string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->ruleDefinition = $ruleDefinition;
    }
}
