<?php
namespace Game;

require_once(dirname(__FILE__).'/simpletest/autorun.php');
require_once(dirname(__FILE__).'/php/Engine.php');
require_once(dirname(__FILE__).'/php/Db.php');

class TestOfEngone extends \UnitTestCase
{
    function test1()
    {
        $engine = new Engine;
        $items  = $engine->getInitialData();
        echo "Init items<br>";
        $this->assertTrue(count($items) == 9, 'should return 9 items');
    }

    function test2()
    {
        $engine = new Engine;
        $items  = $engine->getInitialData(1);
        echo "Select item items<br>";
        $this->assertTrue($items[1] == 1, 'weight should be 1');
    }

    function test3()
    {
        $engine = new Engine;
        $items  = $engine->getInitialData(1);
        echo "Put group of 3 items on scales<br>";
        $items = $engine->putOnScales($items, 3);

        $this->assertTrue(count($items) == 3, 'should be 3 items');
    }

    function test4()
    {
        $engine = new Engine;
        $items  = $engine->getInitialData(1);
        echo "Find the heaviest group<br>";
        $scale = $engine->getHeaviestGroup($items, 3);
        $items = $scale['heaviest'];
        $weight = $engine->getWeight($items);

        $this->assertTrue($weight == 1, 'weight should be 1');
    }
}