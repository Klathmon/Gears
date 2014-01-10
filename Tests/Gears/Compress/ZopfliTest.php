<?php
namespace Gears\Compress;

class ZopfliTest extends \PHPUnit_Framework_TestCase
{
    const DS = DIRECTORY_SEPARATOR;
    
    public function test1is1()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * @dataProvider providerCompressionData
     */
    public function testCompression($type, $level, $fileToCompress, $fileWithExpectedOutput)
    {
        $dataToCompress  = file_get_contents(__DIR__ . self::DS . 'data' . self::DS . $fileToCompress);
        $expectedOutput = (binary)file_get_contents(__DIR__ . self::DS . 'data' . self::DS . $fileWithExpectedOutput);

        $zopfli = new Zopfli($type, $level);
        
        $this->assertInstanceOf('\\Gears\\Compress\\Zopfli', $zopfli);
        
        $actualOutput = $zopfli->compress($dataToCompress);
        
        $this->assertEquals($expectedOutput, $actualOutput);
        
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
            [Zopfli::RFC1950, 05, 'testCompressionData.txt', '05zlibOutput.gz'],
            [Zopfli::RFC1951, 05, 'testCompressionData.txt', '05deflateOutput.gz'],
            [Zopfli::RFC1952, 05, 'testCompressionData.txt', '05gzipOutput.gz'],
            [Zopfli::RFC1950, 15, 'testCompressionData.txt', '15zlibOutput.gz'],
            [Zopfli::RFC1951, 15, 'testCompressionData.txt', '15deflateOutput.gz'],
            [Zopfli::RFC1952, 15, 'testCompressionData.txt', '15gzipOutput.gz'],
            [Zopfli::RFC1950, 50, 'testCompressionData.txt', '50zlibOutput.gz'],
            [Zopfli::RFC1951, 50, 'testCompressionData.txt', '50deflateOutput.gz'],
            [Zopfli::RFC1952, 50, 'testCompressionData.txt', '50gzipOutput.gz'],
        ];
    }
}
 