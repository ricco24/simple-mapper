<?php

namespace SimpleMapper;

interface Structure
{
    /**
     * Fetch row class by table
     * @param string $table
     * @return string
     */
    public function getActiveRowClass($table);

    /**
     * Fetch selection class by table
     * @param string $table
     * @return string
     */
    public function getSelectionClass($table);
}
