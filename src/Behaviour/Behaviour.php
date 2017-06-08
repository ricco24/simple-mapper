<?php

namespace SimpleMapper\Behaviour;

use SimpleMapper\ActiveRow;

interface Behaviour
{
    /**
     * Transform data before insert
     * @param array $data
     * @return array
     */
    public function beforeInsert(array $data): array;

    /**
     * Handle after insert
     * @param ActiveRow $record
     * @param array $data
     */
    public function afterInsert(ActiveRow $record, array $data): void;

    /**
     * Transform data before update
     * @param ActiveRow $record
     * @param array $data
     * @return array
     */
    public function beforeUpdate(ActiveRow $record, array $data): array;

    /**
     * Handle after update
     * @param ActiveRow $oldRecord
     * @param ActiveRow $newRecord
     * @param array $data
     */
    public function afterUpdate(ActiveRow $oldRecord, ActiveRow $newRecord, array $data): void;

    /**
     * Handle before delete
     * @param ActiveRow $record
     * @param bool $soft
     */
    public function beforeDelete(ActiveRow $record, bool $soft): void;

    /**
     * Handle after delete
     * @param ActiveRow $record
     * @param bool $soft
     */
    public function afterDelete(ActiveRow $record, bool $soft): void;
}
