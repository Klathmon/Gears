<?php
namespace Gears\View;

interface ViewInterface {
    public function getMime();
    public function setMime($mimeType);
    public function getOutput();
} 