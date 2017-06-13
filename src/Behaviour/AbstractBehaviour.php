<?php

declare(strict_types=1);

namespace SimpleMapper\Behaviour;

use SimpleMapper\ActiveRow;

abstract class AbstractBehaviour implements Behaviour
{
    /**
     * @inheritdoc
     */
    public function beforeInsert(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function afterInsert(ActiveRow $record, array $data): void
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeUpdate(ActiveRow $record, array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function afterUpdate(ActiveRow $oldRecord, ActiveRow $newRecord, array $data): void
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(ActiveRow $record, bool $soft): void
    {
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(ActiveRow $record, bool $soft): void
    {
    }
}
