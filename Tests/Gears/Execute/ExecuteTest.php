<?php
namespace Gears\Execute\Test;

use Gears\Execute\Execute;
use PHPUnit_Framework_TestCase;

class ExecuteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTestCommands
     */
    public function testExecute($command, $stdin, $expectedStdout, $expectedStderr)
    {        
        $object = new Execute($command);
        
        $this->assertInstanceOf('\\Gears\\Execute\\Execute', $object);
        
        $object->run($stdin);

        $this->assertEquals($expectedStdout, $object->getOutput());
        $this->assertEquals($expectedStderr, $object->getErrorOutput());
    }
    
    public function providerTestCommands()
    {
        $retval = [
            ['echo Test!', null, "Test!", ''],
            ['echo Test! 1>&2', null, '', "Test!"],
        ];
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $retval[] = ['FINDSTR "Test"', "Line1Test" . PHP_EOL . "Line2None" . PHP_EOL . "Line3Test", "Line1Test" . PHP_EOL . "Line3Test", ''];
            $retval[] = ['grep', "Line1Test" . PHP_EOL . "Line2None" . PHP_EOL . "Line3Test", '', '\'grep\' is not recognized as an internal or external command,'
            . PHP_EOL . 'operable program or batch file.'];
        }else{
            $retval[] = ['grep "Test"', "Line1Test" . PHP_EOL . "Line2None" . PHP_EOL . "Line3Test", "Line1Test" . PHP_EOL . "Line3Test", ''];
            $retval[] = ['grep Test rawr', "Line1Test" . PHP_EOL . "Line2None" . PHP_EOL . "Line3Test", '', 'grep: rawr: No such file or directory'];
        }
        
        return $retval;
    }
}