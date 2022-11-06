<?php

declare(strict_types=1);

namespace SimpleMapper\Scope;

class Scope
{
    private string $name;

    /** @var callable */
    private $callback;

    public function __construct(string $name, callable $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }
}
