<?php

class B
{
    /**
     * @var A
     */
    private $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }
}
