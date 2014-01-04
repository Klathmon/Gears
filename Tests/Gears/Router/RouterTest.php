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
    public static function testExceptionOnMissing404Route()
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . 'testMissing404.json');

        self::assertNotInstanceOf('\\Gears\\Router\\Router', $router);
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
    public function testParseRoute($routeFile, $route, $expectedName, $expectedController, $expectedAction, $expectedParameters)
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . $routeFile);

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
            ['testRoutes.json', '/testbaseurl/testroute1', 'test1', 'Test1', 'show', ['param1value' => 'param1value', 'param2value' => 'param2value']],
            ['testRoutes.json', '/testbaseurl/testroute2/123/456', 'test2', 'Test2', 'show', ['value1' => '123', 'value2' => '456']],
            ['testRoutes.json', '/testbaseurl/testroute3/MyController/myAction/myParameter', 'test3', 'MyController', 'myAction', ['param1' => 'myParameter']],
            ['testRoutes.json', '/not/a/real/route', '404', 'ErrorController', 'fourOhFour', []],
            ['testRoutes2.json', '/testroute1', 'test1', 'Test1', 'show', ['param1value' => 'param1value', 'param2value' => 'param2value']],
            ['testRoutes2.json', '/testroute2/123/456', 'test2', 'Test2', 'show', ['value1' => '123', 'value2' => '456']],
            ['testRoutes2.json', '/testroute3/MyController/myAction/myParameter', 'test3', 'MyController', 'myAction', ['param1' => 'myParameter']],
            ['testRoutes2.json', '/not/a/real/route', '404', 'ErrorController', 'fourOhFour', []],
        ];
    }

    /**
     * @dataProvider providerTestReverseRoute
     */
    public function testReverseRoute($routeFile, $routeName, $parameters, $expectedRoute)
    {
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . $routeFile);

        $this->assertInstanceOf('\\Gears\\Router\\Router', $router);

        $reversedRoute = $router->reverseRoute($routeName, $parameters);

        $this->assertEquals($expectedRoute, $reversedRoute);
    }

    public static function providerTestReverseRoute()
    {
        return [
            ['testRoutes.json', 'test1', [], '/testbaseurl/testroute1'],
            ['testRoutes.json', 'test2', ['value1' => 123, 'value2' => '456'], '/testbaseurl/testroute2/123/456'],
            ['testRoutes.json', 'test3', [':controller' => 'MyController', ':action' => 'MyAction', ':param1' => 'MyParameter'], '/testbaseurl/testroute3/MyController/MyAction/MyParameter'],
            ['testRoutes2.json', 'test1', [], '/testroute1'],
            ['testRoutes2.json', 'test2', ['value1' => 123, 'value2' => '456'], '/testroute2/123/456'],
            ['testRoutes2.json', 'test3', [':controller' => 'MyController', ':action' => 'MyAction', ':param1' => 'MyParameter'], '/testroute3/MyController/MyAction/MyParameter'],
        ];
    }

    /**
     * @dataProvider providerTestBadReverseRoute
     */
    public function testBadReverseRoute($expectedException, $routeFile, $routeName, $parameters)
    {
        $this->setExpectedException($expectedException);
        
        $router = new Router(__DIR__ . DIRECTORY_SEPARATOR . $routeFile);

        $this->assertInstanceOf('\\Gears\\Router\\Router', $router);

        $router->reverseRoute($routeName, $parameters);
    }

    public static function providerTestBadReverseRoute()
    {
        return [
            ['InvalidArgumentException', 'testRoutes.json', 'notarealtestroute', []],
            ['\\Gears\\Exceptions\\KeyNotDefinedException', 'testRoutes.json', 'test2', [123, '456']],
            ['\\Gears\\Exceptions\\KeyNotDefinedException', 'testRoutes.json', 'test3', [':controller' => 'MyController', ':action' => 'MyAction']],
            ['InvalidArgumentException', 'testRoutes.json', 'test3', 'cats'],
        ];
    }
}
 