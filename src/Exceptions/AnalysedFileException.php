<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class AnalysedFileException extends Base
{
    private $analysedFile;

    public function __construct(string $analysedFile, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->analysedFile = $analysedFile;
    }

    public function getAnalysedFile()
    {
        return $this->analysedFile;
    }
}
