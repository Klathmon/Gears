<?php
namespace Gears\Compress;

class ZopfliTest extends \PHPUnit_Framework_TestCase
{
    const DS = DIRECTORY_SEPARATOR;

    /**
     * @expectedException \Gears\Exceptions\BinaryErrorException
     */
    public function testBadExecution()
    {
        $zopfli = new Zopfli(ZOPFLI::RFC1951, 0);

        $this->assertInstanceOf('\\Gears\\Compress\\Zopfli', $zopfli);
        
        $zopfli->compress('This is a test and it should fail!');
    }

    /**
     * @dataProvider providerCompressionData
     */
    public function testCompression($type, $level, $fileToCompress)
    {
        $dataToCompress  = file_get_contents(__DIR__ . self::DS . 'data' . self::DS . $fileToCompress);

        $zopfli = new Zopfli($type, $level);
        
        $this->assertInstanceOf('\\Gears\\Compress\\Zopfli', $zopfli);
        
        $actualOutput = $zopfli->compress($dataToCompress);
        
        switch($type){
            case Zopfli::RFC1950:
                $decompressedData = gzuncompress($actualOutput);
                break;
            case Zopfli::RFC1951:
                $decompressedData = gzinflate($actualOutput);
                break;
            case Zopfli::RFC1952:
                $decompressedData = gzdecode($actualOutput);
                break;
        }
        
        $this->assertEquals($dataToCompress, $decompressedData);
    }

    public function providerCompressionData()
    {
        return [
            [Zopfli::RFC1950, 05, 'testCompressionData.txt'],
            [Zopfli::RFC1951, 05, 'testCompressionData.txt'],
            [Zopfli::RFC1952, 05, 'testCompressionData.txt'],
            [Zopfli::RFC1950, 15, 'testCompressionData.txt'],
            [Zopfli::RFC1951, 15, 'testCompressionData.txt'],
            [Zopfli::RFC1952, 15, 'testCompressionData.txt'],
            [Zopfli::RFC1950, 50, 'testCompressionData.txt'],
            [Zopfli::RFC1951, 50, 'testCompressionData.txt'],
            [Zopfli::RFC1952, 50, 'testCompressionData.txt'],
        ];
    }
}
 