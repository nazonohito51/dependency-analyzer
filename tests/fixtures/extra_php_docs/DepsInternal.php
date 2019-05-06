<?php
declare(strict_types=1);

namespace Tests\Fixtures\ExtraPhpDocs;

/**
 * @da-internal \Tests\Fixture\ExtraPhpDocs\SomeClass
 */
class DepsInternal
{
    const NON_DOC = 0;
    /**
     * @da-internal
     */
    const HAVE_DOC = 1;
    /**
     * @da-internal \Tests\Fixture\ExtraPhpDocs\SomeClassForConstant
     */
    const HAVE_DOC_WITH_OPTION = 2;

    private $nonDoc;
    /**
     * @da-internal
     */
    private $haveDoc;
    /**
     * @da-internal \Tests\Fixture\ExtraPhpDocs\SomeClassForProperty
     */
    private $haveDocWithOption;

    private function nonDoc() {}
    /**
     * @da-internal
     */
    private function haveDoc() {}
    /**
     * @da-internal !\Tests\Fixture\ExtraPhpDocs\SomeClassForMethod
     */
    private function haveDocWithOption() {}
}
