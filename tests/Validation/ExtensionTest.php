<?php
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
    public function setUp():void
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidExtension()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Almuth\Upload\Validation\Extension('txt');
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testInvalidExtension()
    {
        $file = new \Almuth\Upload\FileInfo($this->assetsDirectory . '/foo_wo_ext', 'foo_wo_ext');
        $validation = new \Almuth\Upload\Validation\Extension('txt');
        $validation->validate($file); // <-- SHOULD throw exception
    }
}
