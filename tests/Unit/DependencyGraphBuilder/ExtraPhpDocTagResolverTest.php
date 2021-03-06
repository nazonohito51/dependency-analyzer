<?php
declare(strict_types=1);

namespace Tests\Unit\DependencyAnalyzer\DependencyGraphBuilder;

use DependencyAnalyzer\DependencyGraphBuilder\ExtraPhpDocTagResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Tests\Fixtures\ExtraPhpDocs\DepsInternal;
use Tests\TestCase;

class ExtraPhpDocTagResolverTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once $this->getFixturePath('/extra_php_docs/DepsInternal.php');
    }

    /**
     * @throws \ReflectionException
     */
    public function testResolveDepsInternalTag()
    {
        $reflectionClass = new \ReflectionClass(DepsInternal::class);
        $sut = $this->createExtraPhpDocTagResolver();

        $actual = $sut->resolveDepsInternalTag($reflectionClass);

        $this->assertCount(7, $actual);
        $this->assertTrue($actual[0]->getFqsen()->isClass());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal', $actual[0]->getFqsen()->toString());
        $this->assertCount(1, $actual[0]->getTargets());
        $this->assertSame('\Tests\Fixture\ExtraPhpDocs\SomeClass', $actual[0]->getTargets()[0]);
        $this->assertTrue($actual[1]->getFqsen()->isProperty());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::$haveDoc', $actual[1]->getFqsen()->toString());
        $this->assertCount(0, $actual[1]->getTargets());
        $this->assertTrue($actual[2]->getFqsen()->isProperty());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::$haveDocWithOption', $actual[2]->getFqsen()->toString());
        $this->assertCount(1, $actual[2]->getTargets());
        $this->assertSame('\Tests\Fixture\ExtraPhpDocs\SomeClassForProperty', $actual[2]->getTargets()[0]);
        $this->assertTrue($actual[3]->getFqsen()->isMethod());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::haveDoc()', $actual[3]->getFqsen()->toString());
        $this->assertCount(0, $actual[3]->getTargets());
        $this->assertTrue($actual[4]->getFqsen()->isMethod());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::haveDocWithOption()', $actual[4]->getFqsen()->toString());
        $this->assertCount(1, $actual[4]->getTargets());
        $this->assertSame('!\Tests\Fixture\ExtraPhpDocs\SomeClassForMethod', $actual[4]->getTargets()[0]);
        $this->assertTrue($actual[5]->getFqsen()->isClassConstant());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::HAVE_DOC', $actual[5]->getFqsen()->toString());
        $this->assertCount(0, $actual[5]->getTargets());
        $this->assertTrue($actual[6]->getFqsen()->isClassConstant());
        $this->assertSame('\Tests\Fixtures\ExtraPhpDocs\DepsInternal::HAVE_DOC_WITH_OPTION', $actual[6]->getFqsen()->toString());
        $this->assertCount(1, $actual[6]->getTargets());
        $this->assertSame('\Tests\Fixture\ExtraPhpDocs\SomeClassForConstant', $actual[6]->getTargets()[0]);
    }

    protected function createExtraPhpDocTagResolver(): ExtraPhpDocTagResolver
    {
        $lexer = new Lexer();
        $parser = new PhpDocParser(new TypeParser(), new ConstExprParser());

        return new ExtraPhpDocTagResolver($lexer, $parser);
    }
}
