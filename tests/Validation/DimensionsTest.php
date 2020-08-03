<?php

use PHPUnit\Framework\TestCase;

class DimensionsTest extends TestCase
{
    public function setUp():void
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testWidthAndHeight()
    {
        $dimensions = new \Almuth\Upload\Validation\Dimensions(100, 100);
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testWidthDoesntMatch()
    {
        $dimensions = new \Almuth\Upload\Validation\Dimensions(200, 100);
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testHeightDoesntMatch()
    {
        $dimensions = new \Almuth\Upload\Validation\Dimensions(100, 200);
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }
}
