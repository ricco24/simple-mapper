<?php

namespace Kelemen\SimpleMapper\Tests;

require_once 'TestBase.php';

use SimpleMapper\ActiveRow;
use SimpleMapper\Selection;
use SimpleMapper\Structure\CustomStructure;

class StructureTest extends TestBase
{
   public function testCustomStructure()
   {
       $structure = new CustomStructure();
       $structure->registerTable('table1', 'row class table 1', 'select class table 1');
       $structure->registerTable('table2', 'row class table 2', 'select class table 2');

       $this->assertEquals('row class table 1', $structure->getActiveRowClass('table1'));
       $this->assertEquals('row class table 2', $structure->getActiveRowClass('table2'));
       $this->assertEquals('select class table 1', $structure->getSelectionClass('table1'));
       $this->assertEquals('select class table 2', $structure->getSelectionClass('table2'));

       $this->assertEquals(ActiveRow::class, $structure->getActiveRowClass('table3'));
       $this->assertEquals(Selection::class, $structure->getSelectionClass('table3'));
   }

    public function testDefaultsCustomStructure()
    {
        $structure = new CustomStructure();
        $this->assertEquals(ActiveRow::class, $structure->getActiveRowClass('table3'));
        $this->assertEquals(Selection::class, $structure->getSelectionClass('table3'));
    }

    public function testFluentCustomStructure()
    {
        $structure = new CustomStructure();
        $structure
            ->registerTable('table1', 'row class table 1', 'select class table 1')
            ->registerTable('table2', 'row class table 2', 'select class table 2');
    }
}