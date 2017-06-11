<?php

declare(strict_types=1);

namespace SimpleMapper\Scope;

class Scope
{
    /** @var string */
    private $name;

    /** @var callable */
    private $callback;

    /**
     * @param string $name
     * @param callable $callback
     */
    public function __construct(string $name, callable $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }
}
