<?php

declare(strict_types=1);

namespace SimpleMapper\Behaviour;

use Ramsey\Uuid\Uuid;

class Uuid4Behaviour extends AbstractBehaviour
{
    private string $field;

    public function __construct(string $field = 'id')
    {
        $this->field = $field;
    }

    public function beforeInsert(array $data): array
    {
        if (!array_key_exists($this->field, $data) || !$data[$this->field]) {
            $data[$this->field] = Uuid::uuid4();
        }
        return $data;
    }
}
