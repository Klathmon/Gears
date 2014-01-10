<?php
namespace Gears\Compress;

use Gears\Exceptions\BinaryErrorException;
use Gears\Exceptions\FileNotReadableException;
use Gears\Execute\Execute;

class Zopfli
{
    const ZLIB    = self::RFC1950;
    const DEFLATE = self::RFC1951;
    const GZIP    = self::RFC1952;
    const RFC1950 = '--zlib';
    const RFC1951 = '--deflate';
    const RFC1952 = '--gzip';

    protected $compressionLevel;
    protected $compressionType;
    protected $binaryDirectory;
    protected $binary;

    public function __construct($compressionType = self::RFC1952, $compressionLevel = 15)
    {
        $this->compressionLevel = (int)$compressionLevel;
        $this->compressionType  = $compressionType;
        $this->binaryDirectory  = __DIR__ . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR;
        $this->binary           = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'zopfli.exe' : 'zopfli');
        chmod($this->binaryDirectory . $this->binary, 0555);
    }

    public function compress($data)
    {
        $inputFileName = tempnam(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), 'Zopfli');

        file_put_contents($inputFileName, $data);

        $command = '.' . DIRECTORY_SEPARATOR . $this->binary . ' ';
        $command .= $this->compressionType . ' ';
        $command .= '--i' . $this->compressionLevel . ' ';
        $command .= '"' . $inputFileName . '"';

        $execute = new Execute($command, $this->binaryDirectory);

        $execute->run();

        if ($execute->getErrorOutput() != '') {
            throw new BinaryErrorException('Error running ' . $this->binary . ': ' . $execute->getErrorOutput());
        }

        $outputFileName = $inputFileName;

        switch ($this->compressionType) {
            case self::RFC1950:
                $outputFileName .= '.zlib';
                break;
            case self::RFC1951:
                $outputFileName .= '.deflate';
                break;
            case self::RFC1952:
                $outputFileName .= '.gz';
                break;
        }

        $output = file_get_contents($outputFileName);
        
        foreach([$inputFileName, $outputFileName] as $fileName){
            if(is_writable($fileName)){
                unlink($fileName);
            }
        }

        if (($output) === false) {
            throw new FileNotReadableException('File ' . $outputFileName . ' cannot be found!');
        }

        return $output;
    }


} 