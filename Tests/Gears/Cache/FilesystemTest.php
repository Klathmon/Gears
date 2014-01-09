<?php
namespace Gears\Cache;

use DateTime;
use InvalidArgumentException;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $cacheDir = self::getCachePath();

        @self::delTreeHelper($cacheDir);

        @mkdir($cacheDir);
    }

    public static function tearDownAfterClass()
    {
        self::delTreeHelper(self::getCachePath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorBadPath()
    {
        $cache = new Filesystem(self::getCachePath() . 'BADPATH!?');

        $this->assertNotInstanceOf('\\Gears\\Cache\\Filesystem', $cache);
    }

    public function getConstructor()
    {
        $cache = new Filesystem(self::getCachePath());

        $this->assertInstanceOf('\\Gears\\Cache\\Filesystem', $cache);

        return $cache;
    }

    /**
     * @dataProvider providerCacheData
     */
    public function testStore($key, $value, $expectedFilename)
    {
        $cache = $this->getConstructor();

        $cache->store($key, $value);

        $this->assertFileExists(self::getCachePath() . $expectedFilename, 'Failed asserting that file "' . $cache->convertKeyToFileName($key) . '" exists!');
    }

    /**
     * @depends      testStore
     * @dataProvider providerCacheData
     */
    public function testGet($key, $value, $expectedFilename)
    {
        $cache = $this->getConstructor();

        $this->assertFileExists(self::getCachePath() . $expectedFilename);

        $this->assertEquals($value, $cache->fetch($key));
    }

    /**
     * @depends      testGet
     * @dataProvider providerCacheData
     * @noinspection PhpUnusedParameterInspection
     */
    public function testDelete($key, $value, $expectedFilename)
    {
        if($value == $value){}//Get rid of the IDE warning...
        
        $cache = $this->getConstructor();

        $this->assertFileExists(self::getCachePath() . $expectedFilename);

        $cache->delete($key);

        $this->assertNull($cache->fetch($key));

        $this->assertFileNotExists(self::getCachePath() . $expectedFilename);
    }

    public function providerCacheData()
    {
        return [
            [
                'key1',
                'test1',
                '744252496-key1.cache'
            ],
            [
                'key2',
                DateTime::createFromFormat('U', time()),
                '1252706838-key2.cache'
            ],
            [
                ['folder1', 'key3'],
                'test3',
                '1726346163-folder1' . DIRECTORY_SEPARATOR . '1034812036-key3.cache'
            ],
            [
                ['folder1', 'folder2', 'key4'],
                'test4',
                '1726346163-folder1' . DIRECTORY_SEPARATOR . '1269239-folder2' . DIRECTORY_SEPARATOR
                . '1547079903-key4.cache'
            ],
            [
                'spaces in the key name key 5',
                'test5',
                '165524870-spaces_in_the_key_name_ke.cache'
            ],
            [
                ['spaces in the folder', 'key 6'],
                'test5',
                '2057117674-spaces_in_the_folder' . DIRECTORY_SEPARATOR . '623269012-key_6.cache'
            ],
        ];
    }
    
    /**
     * @depends testStore
     **/
    public function testDeleteGroup()
    {
        $cache = $this->getConstructor();
        
        $cache->store(['folder1', 'superKey1!'], 'rawr1');
        $cache->store(['folder2', 'folder3', 'superKey1!'], 'rawr2');
        $cache->store(['folder2', 'folder4', 'superKey1!'], 'rawr3');
        $cache->store(['folder2', 'folder3', 'superKey2!'], 'rawr4');
        $cache->store(['folder2', 'folder4', 'superKey2!'], 'rawr5');
        $cache->store(['folder2', 'superKey!'], 'rawr6');
        
        $cache->delete(['folder2', 'folder4']);
        
        $this->assertNull($cache->fetch(['folder2', 'folder4', 'superKey1!']));
        $this->assertNull($cache->fetch(['folder2', 'folder4', 'superKey2!']));
        
        $this->assertEquals('rawr1', $cache->fetch(['folder1', 'superKey1!']));
        $this->assertEquals('rawr2', $cache->fetch(['folder2', 'folder3', 'superKey1!']));
        $this->assertEquals('rawr4', $cache->fetch(['folder2', 'folder3', 'superKey2!']));
        
        $cache->delete();

        $this->assertNull($cache->fetch(['folder1', 'superKey1!']));
        $this->assertNull($cache->fetch(['folder2', 'folder3', 'superKey1!']));
        $this->assertNull($cache->fetch(['folder2', 'folder4', 'superKey1!']));
        $this->assertNull($cache->fetch(['folder2', 'folder3', 'superKey2!']));
        $this->assertNull($cache->fetch(['folder2', 'folder4', 'superKey2!']));
        $this->assertNull($cache->fetch(['folder2', 'superKey!']));
    }


    protected static function getCachePath()
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'CacheTest' . DIRECTORY_SEPARATOR;
    }
    
    public static function delTreeHelper($dir)
    {
        if ($dir != '') {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? self::delTreeHelper("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }
        return false;
    }
}
 