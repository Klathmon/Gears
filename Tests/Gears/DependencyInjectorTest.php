<?php
namespace Gears;

use DateTime;
use InvalidArgumentException;

class DependencyInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependencyInjector
     */
    protected $di;

    protected function setup()
    {
        $this->di = new DependencyInjector();

        self::assertInstanceOf('\\Gears\\DependencyInjector', $this->di);
    }

    protected function tearDown()
    {
        unset($this->di);
    }

    public function testAddingDependency()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->di->addDependency('testDateTime', function($di){
                return new DateTime('now');
            });
    }
    
    public function testGettingDependencyNewInstance()
    {
        $this->testAddingDependency();
        
        $dateTime = $this->di->getDependency('testDateTime', true);
        
        $this->assertInstanceOf('\\DateTime', $dateTime);
    }
    
    public function testGettingDependencySameInstance()
    {
        $this->testAddingDependency();
        
        $dateTime = $this->di->getDependency('testDateTime', false);
        
        $this->assertInstanceOf('\\DateTime', $dateTime);
    }
    
    public function testGettingDependencySameInstanceMoreThanOnce()
    {
        $this->testAddingDependency();

        /** @var DateTime $dateTime */
        $dateTime = $this->di->getDependency('testDateTime', false);
        
        $this->assertInstanceOf('\\DateTime', $dateTime);
        
        /** @var DateTime $dateTime2 */
        $dateTime2 = $this->di->getDependency('testDateTime', false);
        
        $this->assertInstanceOf('\\DateTime', $dateTime2);
        
        //They both refer to the same object
        $this->assertTrue(($dateTime === $dateTime2));
    }
    
    public function testGettingDependencyNewInstanceMoreThanOnce()
    {
        $this->testAddingDependency();

        /** @var DateTime $dateTime */
        $dateTime = $this->di->getDependency('testDateTime', true);
        
        $this->assertInstanceOf('\\DateTime', $dateTime);
        
        /** @var DateTime $dateTime2 */
        $dateTime2 = $this->di->getDependency('testDateTime', true);
        
        $this->assertInstanceOf('\\DateTime', $dateTime2);
        
        //They both don't refer to the same object
        $this->assertTrue(($dateTime !== $dateTime2));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddingDependencyNonScalarKey()
    {
        $this->di->addDependency(function(){echo 'TEST!';}, 'key');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGettingDependencyNonScalarKey()
    {
        $this->testAddingDependency();
        
        $this->di->getDependency(function(){echo 'TEST!';});
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddingDependencyNonClosure()
    {
        $this->di->addDependency('key', '\\DateTime::createFromFormat');
    }

    /**
     * @expectedException \Gears\Exceptions\KeyNotDefinedException
     */
    public function testGettingDependencyKeyNotDefined()
    {
        $this->testAddingDependency();
        
        $this->di->getDependency('notarealkey!');
    }
}
 