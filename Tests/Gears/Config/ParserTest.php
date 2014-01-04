<?php
namespace Gears\Config;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Gears\Exceptions\FileNotReadableException
     */
    public function testfileNotReadable()
    {
        $config = new Parser('notarealfile.json');

        $this->assertNotInstanceOf('\\Gears\\Config\\Parser', $config);
    }

    /**
     * @expectedException \Gears\Exceptions\UnknownTypeException
     */
    public function testUnknownType()
    {
        $config = new Parser(__DIR__ . DIRECTORY_SEPARATOR . 'config.json', 'yaml');

        $this->assertNotInstanceOf('\\Gears\\Config\\Parser', $config);
    }

    /**
     * @dataProvider providerTestConfigFiles
     */
    public function testConfigFileParsing($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        foreach ($expectedArray as $name => $value) {
            $this->assertTrue(isset($config[$name]));
            $this->assertEquals($value, $config[$name]);
        }
    }

    /**
     * @dataProvider providerTestConfigFiles
     */
    public function testConfigFileIteration($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        foreach ($config as $name => $value) {
            $this->assertEquals($expectedArray[$name], $value);
        }
    }

    /**
     * @dataProvider providerTestConfigFiles
     */
    public function testConfigFileConvertToArray($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        $array = iterator_to_array($config);

        $this->assertInternalType('array', $array);

        $this->assertEquals($expectedArray, $array);
    }

    /**
     * @dataProvider providerTestConfigFiles
     */
    public function testConfigFileCounting($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        $this->assertEquals(count($expectedArray), count($config));
    }
    
    /**
     * @dataProvider providerTestConfigFiles
     * @expectedException \Gears\Exceptions\ImmutableException
     */
    public function testConfigFileExceptionOnSet($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        $config[key($expectedArray)] = 'testingSetting';
    }
    
    /**
     * @dataProvider providerTestConfigFiles
     * @expectedException \Gears\Exceptions\ImmutableException
     */
    public function testConfigFileExceptionOnUnset($fileName, $fileType, $expectedArray)
    {
        $config = $this->makeConstructor($fileName, $fileType);

        unset($config[key($expectedArray)]);
    }

    public function providerTestConfigFiles()
    {
        return [
            [
                'config.json',
                'json',
                [
                    'assoc1' => 'thing1',
                    'assoc2' => ['thing1', 'thing2', 'thing3'],
                    'assoc3' => ['one' => 'thing1', 'two' => 'thing2', 'three' => 'thing3']
                ]
            ],
            [
                'config.ini',
                'ini',
                [
                    'assoc1' => 'thing1',
                    'assoc2' => ['thing1', 'thing2', 'thing3'],
                    'assoc3' => ['one' => 'thing1', 'two' => 'thing2', 'three' => 'thing3']
                ]
            ],
            [
                'config.xml',
                'xml',
                [
                    'assoc1' => 'thing1',
                    'assoc2' => ['thing1', 'thing2', 'thing3'],
                    'assoc3' => ['one' => 'thing1', 'two' => 'thing2', 'three' => 'thing3']
                ]
            ],
        ];
    }


    protected function makeConstructor($fileName, $fileType)
    {
        $config = new Parser(__DIR__ . DIRECTORY_SEPARATOR . $fileName, $fileType);

        $this->assertInstanceOf('\\Gears\\Config\\Parser', $config);

        return $config;
    }
}
 