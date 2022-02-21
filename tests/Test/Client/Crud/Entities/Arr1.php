<?php

namespace tests\RavenDB\Test\Client\Crud\Entities;

class Arr1
{
    private ?array $str = null;

    public function getStr(): ?array
    {
        return $this->str;
    }

    public function setStr(array $str): void
    {
        $this->str = $str;
    }
}
