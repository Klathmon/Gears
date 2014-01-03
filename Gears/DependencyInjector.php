<?php
namespace Gears;

use Closure;
use Gears\Exceptions\KeyNotDefinedException;
use InvalidArgumentException;

class DependencyInjector {
    
    protected $dependencies = [];
    
    protected $instancedDependencies = [];
    
    public function addDependency($key, callable $function)
    {
        if(!is_scalar($key)){
            throw new InvalidArgumentException('Key must be a scalar!');
        }elseif(!(is_object($function) && ($function instanceof Closure))){
            throw new InvalidArgumentException('$function must be a closure!');
        }
        
        $this->dependencies[$key] = $function;
    }
    
    public function getDependency($key, $newInstance = false)
    {
        if(!is_scalar($key)){
            throw new InvalidArgumentException('Key must be a scalar!');
        }elseif(!array_key_exists($key, $this->dependencies)){
            throw new KeyNotDefinedException('The dependency must be defined before it can be used');
        }
        
        if($newInstance === true || !array_key_exists($key, $this->instancedDependencies)){
            $this->instancedDependencies[$key] = $this->dependencies[$key]($this);
        }
        
        return $this->instancedDependencies[$key];
    }
} 