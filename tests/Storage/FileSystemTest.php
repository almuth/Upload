<?php

use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp():void
    {
        // Path to test assets
        $this->assetsDirectory = dirname(__DIR__) . '/assets';

        // Reset $_FILES superglobal
        $_FILES['foo'] = array(
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => 0
        );
    }

    public function testInstantiationWithValidDirectory()
    {
        try {
            /*$storage = $this->getMock(
                '\Almuth\Upload\Storage\FileSystem',
                array('upload'),
                array($this->assetsDirectory)
            );*/
			$storage = $this->createMock(\Almuth\Upload\Storage\FileSystem::class);
			$storage->expects($this->any())->method('upload')->with($this->assetsDirectory);
        } catch(\InvalidArgumentException $e) {
            $this->fail('Unexpected argument thrown during instantiation with valid directory');
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiationWithInvalidDirectory()
    {
        /*$storage = $this->getMock(
            '\Almuth\Upload\Storage\FileSystem',
            array('upload'),
            array('/foo')
        );*/
		$storage = $this->createMock(\Almuth\Upload\Storage\FileSystem::class);
        $storage->expects($this->any())->method('upload')->with('/foo');
    }

    /**
     * Test won't overwrite existing file
     * @expectedException \Almuth\Upload\Exception
     */
    public function testWillNotOverwriteFile()
    {
        $storage = new \Almuth\Upload\Storage\FileSystem($this->assetsDirectory, false);
        $storage->upload(new \Almuth\Upload\FileInfo('foo.txt', dirname(__DIR__) . '/assets/foo.txt'));
    }

    /**
     * Test will overwrite existing file
     */
    public function testWillOverwriteFile()
    {
        /*$storage = $this->getMock(
            '\Almuth\Upload\Storage\FileSystem',
            array('moveUploadedFile'),
            array($this->assetsDirectory, true)
        );*/
		$storage = $this->createMock(\Almuth\Upload\Storage\FileSystem::class);

        $storage->expects($this->any())
                ->method('moveUploadedFile')
			    ->with($this->assetsDirectory, true)
                ->will($this->returnValue(true));

        /*$fileInfo = $this->getMock(
            '\Almuth\Upload\FileInfo',
            array('isUploadedFile'),
            array(dirname(__DIR__) . '/assets/foo.txt', 'foo.txt')
        );*/
		$fileInfo = $this->createMock(\Almuth\Upload\FileInfo::class);
        $fileInfo->expects($this->any())
             ->method('isUploadedFile')
			 ->with(dirname(__DIR__) . '/assets/foo.txt', 'foo.txt')
             ->will($this->returnValue(true));

        $storage->upload($fileInfo);
    }
}
