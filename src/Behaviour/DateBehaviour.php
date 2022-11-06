<?php

declare(strict_types=1);

namespace SimpleMapper\Behaviour;

use Nette\Utils\DateTime;
use SimpleMapper\ActiveRow;

class DateBehaviour extends AbstractBehaviour
{
    private string $createdAtField;

    private string $updatedAtField;

    public function __construct(string $createdAtField = 'created_at', string $updatedAtField = 'updated_at')
    {
        $this->createdAtField = $createdAtField;
        $this->updatedAtField = $updatedAtField;
    }

    public function beforeInsert(array $data): array
    {
        $now = new DateTime('now');
        if ($this->createdAtField) {
            $data[$this->createdAtField] = $now;
        }
        if ($this->updatedAtField) {
            $data[$this->updatedAtField] = $now;
        }
        return $data;
    }

    public function beforeUpdate(ActiveRow $record, array $data): array
    {
        if ($this->updatedAtField) {
            $data[$this->updatedAtField] = new DateTime('now');
        }
        return $data;
    }
}
