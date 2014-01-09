<?php
namespace Gears\Cache;

use DateTime;
use DirectoryIterator;
use Gears\Exceptions\FileNotWritableException;
use InvalidArgumentException;

class Filesystem implements CacheInterface
{
    const EXTENSION = '.cache';
    
    protected $rootPath;
    
    public function __construct($prefix)
    {
        if(($path = realpath($prefix)) === false){
            throw new InvalidArgumentException('Invalid Path provided.');
        }elseif(!is_writable($path)){
            throw new FileNotWritableException('Cache Path must be writable by PHP!');//@codeCoverageIgnore
        }else{
            $this->rootPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
    }

    public function store($key, $value)
    {
        $fileName = $this->convertKeyToFileName($key) . self::EXTENSION;
        
        if(!is_writable(dirname($fileName))){
            mkdir(dirname($fileName), 0777, true);
        }
        
        if(file_put_contents($fileName, serialize($value)) === false){
            throw new FileNotWritableException('Cannot write to cache!');//@codeCoverageIgnore
        }
    }

    public function fetch($key)
    {
        $fileName = $this->convertKeyToFileName($key) . self::EXTENSION;
        
        if(!is_readable($fileName)){
            return null;
        }else{
            return unserialize(file_get_contents($fileName));
        }
    }

    public function delete($key = null)
    {
        if(is_null($key)){
            $fileName = $this->rootPath;
        }else{
            $fileName = $this->convertKeyToFileName($key);
        }
        
        if(is_dir($fileName)){
            $clearDirectory
                = function($directory) use (&$clearDirectory){
                foreach (new DirectoryIterator($directory) as $fileInfo) {
                    /** @var $fileInfo DirectoryIterator */
                    if(!$fileInfo->isDot()){
                        if($fileInfo->isFile()){
                            unlink($fileInfo->getPathname());
                        }elseif($fileInfo->isDir()){
                            $clearDirectory($fileInfo->getPathname());
                        }
                    }
                }
            };
            $clearDirectory($fileName);
        }else{
            unlink($fileName . self::EXTENSION);
        }
    }
    
    public function getLastModifiedTime($key)
    {
        $fileName = $this->convertKeyToFileName($key) . self::EXTENSION;
        
        if(!is_readable($fileName)){
            return DateTime::createFromFormat('U', 0);
        }else{
            return DateTime::createFromFormat('U', filemtime($fileName));
        }
    }
    
    protected function convertKeyToFileName($key)
    {
        $fileName = rtrim($this->rootPath, DIRECTORY_SEPARATOR);
        
        $key = (is_array($key) ? $key : [$key]);
        
        foreach($key as $keyItem){
            $fileName .= DIRECTORY_SEPARATOR;
            $fileName .= md5($keyItem) . '-';
            $fileName .= preg_replace('/[^a-zA-Z0-9.]+/', '_', substr($keyItem, 0, 25));
        }
        
        return $fileName;
    }
} 