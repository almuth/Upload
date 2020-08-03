<?php
use PHPUnit\Framework\TestCase;
class SizeTest extends TestCase
{
    public function setUp():void
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidFileSize()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Size(500);
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    public function testValidFileSizeWithHumanReadableArgument()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Size('500B');
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testInvalidFileSize()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Size(400);
        $validation->validate($file); // <-- SHOULD throw exception
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testInvalidFileSizeWithHumanReadableArgument()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Size('400B');
        $validation->validate($file); // <-- SHOULD throw exception
    }
}
