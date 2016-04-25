<?php

namespace Library\DataMapper\Persisters;

class EntityPersister extends BasePersister
{
    protected $inserts = [];

    protected $removals = [];

    protected $updates = [];

    public function addInsert($oid, array $data)
    {
        $this->inserts[$oid] = $data;
    }

    public function addRemoval($id)
    {
        $this->removals[] = $id;
    }

    public function addUpdate($id, $updateData)
    {
        $this->updates[$id] = $updateData;
    }

    private function clearInserts()
    {
        $this->inserts = [];
    }

    private function clearRemovals()
    {
        $this->removals = [];
    }

    private function clearUpdates()
    {
        $this->updates = [];
    }

    public function executeInserts()
    {
        if (sizeof($this->inserts) == 1)
        {
            return $this->executeSingleInsert();
        }

        return $this->executeBatchInsert();
    }

    private function executeSingleInsert()
    {
        $id = $this->queryBuilder->table($this->metadata->table())
            ->insert(reset($this->inserts));

        $oid = key($this->inserts);

        $this->clearInserts();

        return [$oid => $id];
    }

    private function executeBatchInsert()
    {
        $ids = $this->queryBuilder->table($this->metadata->table())
            ->insertMany($this->inserts);

        $this->clearInserts();

        return $ids;
    }

    public function executeRemovals()
    {
        if (sizeof($this->removals) == 1)
        {
            $this->executeSingleRemoval();
            return;
        }

        $this->executeBatchRemoval();
    }

    private function executeSingleRemoval()
    {
        $this->queryBuilder->table($this->metadata->table())
            ->where($this->metadata->primaryKey()->name(), '=', reset($this->removals))
            ->delete();

        $this->clearRemovals();
    }

    private function executeBatchRemoval()
    {
        $this->queryBuilder->table($this->metadata->table())
            ->where($this->metadata->primaryKey()->name(), 'in', '('.implode(',', $this->removals).')')
            ->delete();

        $this->clearRemovals();
    }

    public function executeUpdates()
    {
        if (sizeof($this->updates) == 1)
        {
            $this->executeSingleUpdate();
            return;
        }

        $this->executeBatchUpdate();
    }

    private function executeSingleUpdate()
    {
        $this->queryBuilder->table($this->metadata->table())
            ->where($this->metadata->primaryKey()->name(), '=', key($this->updates))
            ->update(reset($this->updates), $this->metadata->primaryKey()->name());
    }

    private function executeBatchUpdate()
    {
        $ids = $updateSet = [];
        foreach ($this->updates as $id => $updateData)
        {
            $ids[] = $id;
            foreach ($updateData as $field => $value)
            {
                $updateSet[$field][$id] = $value;
            }
        }

        $this->queryBuilder->table($this->metadata->table())
            ->where($this->metadata->primaryKey()->name(), 'in', '('.implode(',', $ids).')')
            ->updateMany($updateSet, $this->metadata->primaryKey()->name());
    }
}