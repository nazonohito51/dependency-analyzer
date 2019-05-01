<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Exceptions;

class AnalyzedFileException extends Base
{
    private $analyzedFile;

    public function __construct(string $analyzedFile, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->analyzedFile = $analyzedFile;
    }

    public function getAnalyzedFile()
    {
        return $this->analyzedFile;
    }
}
