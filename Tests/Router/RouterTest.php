<?php
namespace Gears\Router;

use InvalidArgumentException;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnBadConstructor()
    {
        /** @noinspection PhpParamsInspection */
        $router = new Router(5);

        $this->assertNotInstanceOf('\\Gears\\Router\\Router', $router);
    }

    /**
     * @expectedException \Gears\Exceptions\FileNotReadableException
     */
    public function testExceptionOnRoutesFileNotFound()
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . 'notARealFile.json');

        $this->assertNotInstanceOf('\\Gears\\Router\\Router', $router);
    }

    /**
     * @expectedException \Gears\Exceptions\InvalidFileFormatException
     */
    public static function testExceptionOnIncorrectJsonFormat()
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . 'testBadRoutes.json');

        self::assertNotInstanceOf('\\Gears\\Router\\Router', $router);
    }
    
    /**
     * @dataProvider providerTestParseRoute
     */
    public function testParseRoute($route, $expectedName, $expectedController, $expectedAction, $expectedParameters)
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . 'testRoutes.json');

        $this->assertInstanceOf('\\Gears\\Router\\Router', $router);

        $parsedRoute = $router->parseRoute($route);

        $this->assertEquals($expectedName, $parsedRoute['name']);
        $this->assertEquals($expectedController, $parsedRoute['controller']);
        $this->assertEquals($expectedAction, $parsedRoute['action']);
        $this->assertEquals($expectedParameters, $parsedRoute['parameters']);
    }

    public static function providerTestParseRoute()
    {
        return [
            ['/testroute1', 'test1', 'Test1', 'show', ['param1value' => 'param1value', 'param2value' => 'param2value']],
            ['/testroute2/123/456', 'test2', 'Test2', 'show', ['value1' => '123', 'value2' => '456']],
            ['/testroute3/MyController/myAction/myParameter', 'test3', 'MyController', 'myAction', ['param1' => 'myParameter']],
            ['/not/a/real/route', '404', 'ErrorController', 'fourOhFour', []],
        ];
    }
}
 