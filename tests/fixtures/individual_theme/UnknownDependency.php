<?php
declare(strict_types=1);

namespace Tests\Fixtures\IndividualTheme;

function hoge() {}

class UnknownDependency
// Unknown Extends/Implement is not resolved by PHPStan, because it attempt resolve them while resolving UnknownDependency class.
// extends \UnknownExtends implements \UnkownImplements
{
    public function __construct()
    {
        new \UnknownNew();
    }

    public function someMethod1(\UnknownProperty $unknown)
    {
        $unknown->someProperty;
    }

    public function someMethod2(\UnknownMethod $unknown)
    {
        $unknown->someMethod();
    }

    public function someMethod3()
    {
        \UnknownClassMethod::someMethod();
    }
}
