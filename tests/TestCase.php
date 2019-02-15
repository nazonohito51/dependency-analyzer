<?php
namespace Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function getFixturePath($name)
    {
        return __DIR__ . '/fixtures' . (substr($name, 0, 1) === '/' ? $name : "/{$name}");
    }
}
