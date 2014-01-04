<?php
namespace Gears\Config;

use ArrayAccess;
use Gears\Exceptions\FileNotReadableException;
use Gears\Exceptions\ImmutableException;
use Gears\Exceptions\UnknownTypeException;

class Parser implements ArrayAccess
{
    private $configData;

    public function __construct($configFileName, $fileType = 'json')
    {
        if (!is_readable($configFileName) || ($fileContents = file_get_contents($configFileName)) === false) {
            throw new FileNotReadableException('Cannot read configuration file!');
        }

        /* These are in order of recommended-ness (most recommended to least recommended)*/
        switch (strtolower($fileType)) {
            case 'json':
                $configData = json_decode($fileContents, true);
                break;
            case 'ini':
                $configData = parse_ini_string($fileContents, true);
                break;
            case 'xml':
                $configData = json_decode(json_encode((array)simplexml_load_string($fileContents)), true);
                break;
            default:
                throw new UnknownTypeException('Unknown file type!');
        }

        $this->configData = $configData;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->configData);
    }

    public function offsetGet($offset)
    {
        return $this->configData[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new ImmutableException('Cannot modify config options, they are read only!');
    }

    public function offsetUnset($offset)
    {
        throw new ImmutableException('Cannot modify config options, they are read only!');
    }
} 