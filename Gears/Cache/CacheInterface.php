<?php
namespace Gears\Cache;

interface CacheInterface
{
    public function __construct($prefix);
    public function fetch($key);
    public function store($key, $value);
    public function delete($key);
} 