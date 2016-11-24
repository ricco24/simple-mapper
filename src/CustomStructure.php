<?php

namespace SimpleMapper;

class CustomStructure implements Structure
{
    /** @var array */
    protected $data = [];

    /**
     * Add row class to table
     * @param string $table
     * @param string $class
     * @return CustomStructure
     */
    public function addActiveRowClass($table, $class)
    {
        $this->data[$table]['row'] = $class;
        return $this;
    }

    /**
     * Add selection class to table
     * @param string $table
     * @param string $class
     * @return CustomStructure
     */
    public function addSelectionClass($table, $class)
    {
        $this->data[$table]['selection'] = $class;
        return $this;
    }

    /**
     * Fetch row class by table
     * @param string $table
     * @return string
     */
    public function getActiveRowClass($table)
    {
        return isset($this->data[$table]['row'])
            ? $this->data[$table]['row']
            : ActiveRow::class;
    }

    /**
     * Fetch selection class by table
     * @param string $table
     * @return string
     */
    public function getSelectionClass($table)
    {
        return isset($this->data[$table]['selection'])
            ? $this->data[$table]['selection']
            : Selection::class;
    }
}
