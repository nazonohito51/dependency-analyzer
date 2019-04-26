<?php
declare(strict_types=1);

namespace Tests\Fixtures\ExtraPhpDocs;

/**
 * @deps-internal \Tests\Fixture\ExtraPhpDocs\SomeClass
 */
class DepsInternal
{
    const NON_DOC = 0;
    /**
     * @deps-internal
     */
    const HAVE_DOC = 1;
    /**
     * @deps-internal \Tests\Fixture\ExtraPhpDocs\SomeClassForConstant
     */
    const HAVE_DOC_WITH_OPTION = 2;

    private $nonDoc;
    /**
     * @deps-internal
     */
    private $haveDoc;
    /**
     * @deps-internal \Tests\Fixture\ExtraPhpDocs\SomeClassForProperty
     */
    private $haveDocWithOption;

    private function nonDoc() {}
    /**
     * @deps-internal
     */
    private function haveDoc() {}
    /**
     * @deps-internal \Tests\Fixture\ExtraPhpDocs\SomeClassForMethod
     */
    private function haveDocWithOption() {}
}
