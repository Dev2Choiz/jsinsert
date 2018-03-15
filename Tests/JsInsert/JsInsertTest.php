<?php

namespace Tests\JsInsert;

use JsInsert\JsInsert;

class JsInsertTest extends \PHPUnit\Framework\TestCase
{
    /** @var  JsInsert $svcJsInsert */
    protected $svcJsInsert;


    public function setUp()
    {
        $this->svcJsInsert = new JsInsert();
        $this->svcJsInsert->reset();
    }

    public function testReset()
    {
        $this->svcJsInsert->addScript('script');
        $this->svcJsInsert->addCssVariable('var', 'value');
        $this->svcJsInsert->addVariable('string', 'var', 'value');

        $this->assertNotEmpty($this->svcJsInsert::$scripts);
        $this->assertNotEmpty($this->svcJsInsert::$variables);
        $this->assertNotEmpty($this->svcJsInsert::$cssVariables);

        $this->svcJsInsert->reset();
        $this->assertEmpty($this->svcJsInsert::$scripts);
        $this->assertEmpty($this->svcJsInsert::$variables);
        $this->assertEmpty($this->svcJsInsert::$cssVariables);
    }

    public function testAddScript()
    {
        $script = 'console.log("yep");';
        $this->svcJsInsert->addScript($script);
        $this->assertEquals($script, $this->svcJsInsert::$scripts[0]);
    }

    public function testAddVariable()
    {
        $this->svcJsInsert->addVariable('string', 'var', 'value');
        $this->assertEquals('string', $this->svcJsInsert::$variables[0]['type']);
        $this->assertEquals('var', $this->svcJsInsert::$variables[0]['name']);
        $this->assertEquals('value', $this->svcJsInsert::$variables[0]['value']);
    }

    public function testAddCssVariable()
    {
        $this->svcJsInsert->addCssVariable('var', 'value');
        $this->assertEquals('var', $this->svcJsInsert::$cssVariables[0]['name']);
        $this->assertEquals('value', $this->svcJsInsert::$cssVariables[0]['value']);
    }

    public function testRender()
    {
        $this->svcJsInsert->addScript('console.log("yep");');
        $this->svcJsInsert->addVariable('string', 'jsVar', 'value');
        $this->svcJsInsert->addCssVariable('cssVar', 'value');
        $expected = <<<EOF
<style>
body {
\t--cssVar: value;
}
</style>
<script>

jsVar="value";
console.log("yep");
</script>

EOF;
        $result = $this->svcJsInsert->renderView();
        $this->assertEquals($expected, $result);
    }

    public function testDeclareNodes()
    {
        $input = [
            ['type' => 'string', 'name' => 'objet.voiture.renault',    'value' => 'value', 'withRoot' => true],
            ['type' => 'string', 'name' => 'objet.voiture.peugeot',    'value' => 'value', 'withRoot' => true],
            ['type' => 'string', 'name' => 'objet.voiture.citroen.c3', 'value' => 'value', 'withRoot' => true],
            ['type' => 'string', 'name' => 'objet.voiture.citroen.c4', 'value' => 'value', 'withRoot' => true],
            ['type' => 'string', 'name' => 'avion',                    'value' => 'value', 'withRoot' => true],
            ['type' => 'string', 'name' => 'bateau',                   'value' => 'value', 'withRoot' => true],
        ];
        $expected = <<<EOF
objet={};
objet.voiture={};
objet.voiture.citroen={};

EOF;
        $result = $this->svcJsInsert->declareNodes($input);
        $this->assertEquals($expected, $result);
    }

    public function testDeclareNodesWithEmptyArrayInput()
    {
        $input = [];
        $expected = '';
        $result = $this->svcJsInsert->declareNodes($input);
        $this->assertEquals($expected, $result);
    }

    public function testPrepareAllParents()
    {
        $variable = 'root.lvl1.lvl2.lvl3';
        $result = $this->svcJsInsert->prepareAllParents($variable);

        $this->assertEquals('root.lvl1.lvl2', $result[0]);
        $this->assertEquals('root.lvl1', $result[1]);
        $this->assertEquals('root', $result[2]);
        $this->assertEquals('root', $result[3]);
    }

    public function testPrepareAllParentsWithEmptyString()
    {
        $variable = 'variableName';
        $result = $this->svcJsInsert->prepareAllParents($variable);

        $this->assertEquals(array('variableName'), $result);
    }
}
