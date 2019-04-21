<?php
declare(strict_types=1);

namespace Tests\Fixtures\AllTheme;

// Dependency analyzer will not analyse 'use' statements.
use Tests\Fixtures\AllTheme\Foundations\NonExistClass;
use Tests\Fixtures\AllTheme\Foundations\ParentClass;
use Tests\Fixtures\AllTheme\Foundations\SomeClass1;
use Tests\Fixtures\AllTheme\Foundations\SomeClass10;
use Tests\Fixtures\AllTheme\Foundations\SomeClass11;
use Tests\Fixtures\AllTheme\Foundations\SomeClass12;
use Tests\Fixtures\AllTheme\Foundations\SomeClass13;
use Tests\Fixtures\AllTheme\Foundations\SomeClass15;
use Tests\Fixtures\AllTheme\Foundations\SomeClass16;
use Tests\Fixtures\AllTheme\Foundations\SomeClass17;
use Tests\Fixtures\AllTheme\Foundations\SomeClass2;
use Tests\Fixtures\AllTheme\Foundations\SomeClass3;
use Tests\Fixtures\AllTheme\Foundations\SomeClass4;
use Tests\Fixtures\AllTheme\Foundations\SomeClass7;
use Tests\Fixtures\AllTheme\Foundations\SomeClass8;
use Tests\Fixtures\AllTheme\Foundations\SomeClass9;
use Tests\Fixtures\AllTheme\Foundations\SomeException1;
use Tests\Fixtures\AllTheme\Foundations\SomeException2;
use Tests\Fixtures\AllTheme\Foundations\SomeInterface;
use Tests\Fixtures\AllTheme\Foundations\SomeTrait;

class AllTheme extends ParentClass implements SomeInterface         // extends(=ParentClass), implements(=SomeInterface)
{
    use SomeTrait;                                                  // use trait(=SomeTrait)

    /**
     * @var SomeClass1 $someClass1
     */
    private $someClass1;

    public function __construct()
    {
        $this->someClass1 = new SomeClass1(SomeClass2::STATUS_OK);  // new(=SomeClass1), fetch public constant(=SomeClass2)
    }

    public function testMethod1(SomeClass3 $someClass3): SomeClass4 // type hinting(=SomeClass3), return type declarations(=SomeClass4)
    {
        try {
            $unknownClass1 = $someClass3->getUnknownClass();        // (getUnknownClass() will return SomeClass5 object)
            $unknownClass2 = $this->someClass1->someMethod(         // (someMethod() will return SomeClass6 object. In this case, you need phpdoc of $someClass1...)
                $unknownClass1->property                            // fetch public property(=SomeClass5)
            );

            if (!$unknownClass1->isStatusOk()) {                    // call public method(=SomeClass6)
                throw new SomeException1();                         // throw(=SomeException1)
            }

            return $unknownClass2->getSomeClass4();
        } catch (SomeException2 $e) {                               // catch(=SomeException2)
            // error handling
        }
    }

    /**
     * @param SomeClass7|SomeClass8 $unknownClass                   // type hinting by phpdoc(=SomeClass7, SomeClass8)
     * @return SomeClass9|SomeClass10                               // return value declarations by phpdoc(=SomeClass9, SomeClass10)
     */
    public function testMethod2($unknownClass)
    {
        return $unknownClass->someMethod();
    }

    public function testMethod3()
    {
        $unknownClass3 = SomeClass11::someMethod();                 // call class method(=SomeClass11)

        if ($unknownClass3 instanceof SomeClass12) {                // instanceof(=SomeClass12)
            return $unknownClass3;
        }

        return null;
    }

    public function testMethod4()
    {
        $ret = [];
        $array = [new SomeClass13, new SomeClass13, new SomeClass13];

        foreach ($array as $item) {
            $ret[] = $item->someMethod();                           // foreach access, (new SomeClass13)->someMethod() will return SomeClass14 object(=SomeClass14)
        }

        return $ret;
    }

    public function testMethod5()
    {
        $array = [new SomeClass15, new SomeClass16, new SomeClass17];

        return $array[1]->someMethod();                             // array dim fetch, (new SomeClass16)->someMethod() will return SomeClass18 object(=SomeClass18)
    }

    public function testMethod6()
    {
        return Foundations\some_function();                         // some_function() will return SomeClass19 object(=SomeClass19)
    }
}
