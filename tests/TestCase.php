<?php
declare(strict_types=1);

namespace Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->getTmpDir(), [$this->getTmpDir() . '.gitkeep']);
    }

    protected function cleanDirectory($directory, array $excludeFiles = [])
    {
        foreach(new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            if (!in_array($file->getPathname(), $excludeFiles)) {
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                } else {
                    @unlink($file->getPathname());
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getRootDir(): string
    {
        return realpath(__DIR__ . '/../');
    }

    /**
     * @param $name
     * @return string
     */
    public function getFixturePath($name): string
    {
        return $this->getRootDir() . '/tests/fixtures' . (substr($name, 0, 1) === '/' ? $name : "/{$name}");
    }

    /**
     * @return string
     */
    protected function getTmpDir(): string
    {
        return $this->getRootDir() . '/tests/tmp/';
    }
}
