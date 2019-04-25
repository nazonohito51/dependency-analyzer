<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class InvalidFullyQualifiedStructureElementNameException extends Base
{
    /**
     * @var string
     */
    private $elementName;

    public function __construct(string $elementName, string $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->elementName = $elementName;
    }

    public function getInvalidElementName(): string
    {
        return $this->elementName;
    }
}
