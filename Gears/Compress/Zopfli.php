<?php
namespace Gears\Compress;

use Gears\Exceptions\BinaryErrorException;
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
        $tmpHandle = tmpfile();
        fwrite($tmpHandle, $data);

        $command = '.' . DIRECTORY_SEPARATOR . $this->binary . ' ';
        $command .= '-c ';
        $command .= $this->compressionType . ' ';
        $command .= '-i' . $this->compressionLevel . ' ';
        $command .= '"' . stream_get_meta_data($tmpHandle)['uri'] . '"';

        $execute = new Execute($command, $this->binaryDirectory);

        $execute->run();

        if ($execute->getErrorOutput() != '') {
            throw new BinaryErrorException('Error running ' . $this->binary . ': ' . $execute->getErrorOutput() . ' ' . $command);
        }

        return $execute->getOutput();
    }


} 