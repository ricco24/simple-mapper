<?php

namespace SimpleMapper\Behaviour;

use Nette\Utils\DateTime;
use SimpleMapper\ActiveRow;

class DateBehaviour extends AbstractBehaviour
{
    /** @var string */
    private $createdAtField;

    /** @var string */
    private $updatedAtField;

    /**
     * @param string $createdAtField
     * @param string $updatedAtField
     */
    public function __construct(string $createdAtField = 'created_at', string $updatedAtField = 'updated_at')
    {
        $this->createdAtField = $createdAtField;
        $this->updatedAtField = $updatedAtField;
    }

    /**
     * @param array $data
     * @return array
     */
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

    /**
     * @param ActiveRow $record
     * @param array $data
     * @return array
     */
    public function beforeUpdate(ActiveRow $record, array $data): array
    {
        if ($this->updatedAtField) {
            $data[$this->updatedAtField] = new DateTime('now');
        }
        return $data;
    }
}
