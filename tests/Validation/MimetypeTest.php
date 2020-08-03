<?php
use PHPUnit\Framework\TestCase;

class MimetypeTest extends TestCase
{
    public function setUp():void
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidMimetype()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Mimetype(array('text/plain'));
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testInvalidMimetype()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Mimetype(array('image/png'));
        $validation->validate($file); // <-- SHOULD throw exception
    }
}
