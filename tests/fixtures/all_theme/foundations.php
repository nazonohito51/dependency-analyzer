<?php
namespace Tests\Fixtures\AllTheme\Foundations;

abstract class ParentClass {}
interface SomeInterface {}
trait SomeTrait {}

class SomeException1 extends \Exception {}
class SomeException2 extends \Exception {}

class SomeClass1 {
    public function __construct($test) {}
    public function someMethod($test): SomeClass6 {}
}
class SomeClass2 {
    const STATUS_OK = 1;
}
class SomeClass3 {
    public function getUnknownClass(): SomeClass5 {}
}
class SomeClass4 {}
class SomeClass5 {
    public $property;
    public function isStatusOk(): bool {}
}
class SomeClass6 {
    public function isStatusOk(): bool {}
    public function getSomeClass4(): SomeClass4 {}
}
class SomeClass7 {
    public function someMethod(): SomeClass9 {}
}
class SomeClass8 {
    public function someMethod(): SomeClass10 {}
}
class SomeClass9 {}
class SomeClass10 {}
class SomeClass11 {
    public static function someMethod() {}
}
class SomeClass12 {}
class SomeClass13 {
    public function someMethod(): SomeClass14 {}
}
class SomeClass14 {}
class SomeClass15 {}
class SomeClass16 {
    public function someMethod(): SomeClass18 {}
}
class SomeClass17 {}
class SomeClass18 {}
class SomeClass19 {}

function some_function(): SomeClass19 {}
