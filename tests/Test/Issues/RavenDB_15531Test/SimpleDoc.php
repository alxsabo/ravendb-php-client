<?php

namespace tests\RavenDB\Test\Issues\RavenDB_15531Test;

class SimpleDoc
{
    private ?string $id = null;
    private ?string $name = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
