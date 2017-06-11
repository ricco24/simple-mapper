<?php

namespace Kelemen\SimpleMapper\Tests\Structure;

require_once __DIR__ . '/../TestBase.php';

use Kelemen\SimpleMapper\Tests\TestBase;
use SimpleMapper\ActiveRow;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;
use SimpleMapper\Structure\BaseStructure;

class BaseStructureTest extends TestBase
{
   public function testActiveRowAndSelectionRegistration()
   {
       $structure = new BaseStructure();
       $structure->registerActiveRowClass('table1', 'row class table 1');
       $structure->registerSelectionClass('table1', 'select class table 1');
       $structure->registerActiveRowClass('table2', 'row class table 2');
       $structure->registerSelectionClass('table2', 'select class table 2');

       $this->assertEquals('row class table 1', $structure->getActiveRowClass('table1'));
       $this->assertEquals('row class table 2', $structure->getActiveRowClass('table2'));
       $this->assertEquals('select class table 1', $structure->getSelectionClass('table1'));
       $this->assertEquals('select class table 2', $structure->getSelectionClass('table2'));

       $this->assertEquals(ActiveRow::class, $structure->getActiveRowClass('table3'));
       $this->assertEquals(Selection::class, $structure->getSelectionClass('table3'));
   }

    public function testDefaults()
    {
        $structure = new BaseStructure();
        $this->assertEquals(ActiveRow::class, $structure->getActiveRowClass('table3'));
        $this->assertEquals(Selection::class, $structure->getSelectionClass('table3'));
    }

    public function testFluentInterface()
    {
        $structure = new BaseStructure();
        $this->assertInstanceOf(BaseStructure::class, $structure->registerActiveRowClass('table1', 'row class table 1'));
        $this->assertInstanceOf(BaseStructure::class,  $structure->registerActiveRowClass('table2', 'row class table 2'));
    }

    public function testScopes()
    {
        $structure = new BaseStructure();
        $structure->registerScopes('table1', [
            new Scope('admin', function () {
                return [
                    'table_1.is_deleted' => false
                ];
            })
        ]);

        $this->assertCount(1, $structure->getScopes('table1'));
        $this->assertEmpty($structure->getScopes('table2'));
        $this->assertInstanceOf(Scope::class, $structure->getScope('table1', 'admin'));
        $this->assertNull($structure->getScope('table1', 'unknown'));
        $this->assertNull($structure->getScope('table2', 'admin'));
    }
}