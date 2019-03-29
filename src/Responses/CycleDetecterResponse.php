<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Responses;

class CycleDetecterResponse implements \Countable
{
    private $cycles = [];

    public function addCycle(array $classes)
    {
        $this->cycles[] = $classes;
    }

    public function getCycles(): array
    {
        return $this->cycles;
        /**
         * Hoge component rule violations
         * | hoge.php | Hoge | -> | fuga.php | Fuga |
         * | hoge.php | Hoge | -> | fuga.php | Fuga |
         *
         * | No | file path |
         * |---|---|
         * | 1  | hoge.php |
         * | 2  | fuga.php |
         */
    }

    public function count()
    {
        return count($this->cycles);
    }
}
