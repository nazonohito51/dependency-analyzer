<?php

class C
{
    /**
     * @var B
     */
    private $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }
}
