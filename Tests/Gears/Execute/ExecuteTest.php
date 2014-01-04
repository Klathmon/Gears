<?php
namespace Gears\Execute\Test;

use Exception;
use Gears\Execute\Execute;
use PHPUnit_Framework_TestCase;

class ExecuteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $command
     * @param string $stdin
     * @param string $expectedStdout
     * @param string $expectedStderr
     *
     * @dataProvider providerTestRunGrepOnString
     */
    public function testRunGrepOnString($command, $stdin, $expectedStdout, $expectedStderr)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Windows Tests will be added later');
        }
        
        $object = new Execute($command);
        $this->assertInstanceOf('\\Gears\\Execute\\Execute', $object);
        $object->execute($stdin);

        $this->assertEquals($expectedStdout, $object->getOutput());
        $this->assertEquals($expectedStderr, $object->getErrorOutput());
    }
    
    public function providerTestRunGrepOnString()
    {
        return [
            ['echo \'Test!\'', null, 'Test!', ''],
            ['echo \'Test!\' 1>&2', null, '', 'Test!'],
            ['grep "Test"', "Line1Test\nLine2None\nLine3Test", "Line1Test\nLine3Test", ''],
            ['grep Test rawr', "Line1Test\nLine2None\nLine3Test", '', 'grep: rawr: No such file or directory'],
        ];
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionOnBlankConstructor()
    {
        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnusedLocalVariableInspection */
        $object = new Execute();
    }
}