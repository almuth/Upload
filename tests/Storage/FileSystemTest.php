<?php

use Almuth\Upload\Exception as UploadException;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Storage\FileSystem;
use PHPUnit\Framework\TestCase;

class FileSystemTest extends TestCase
{
  private string $assetsDirectory;
  private string $uploadDir;

  /**
   * Setup (each test)
   */
  public function setUp(): void
  {
    // Path to test assets
    $this->assetsDirectory = dirname(__DIR__) . '/assets';
    $this->uploadDir = $this->assetsDirectory . '/uploads';
    if ( !is_dir($this->uploadDir)){
      mkdir($this->uploadDir, 0777, true);
    }

    foreach(glob($this->uploadDir . '/*') as $uf){
      unlink($uf);
    }

    // Reset $_FILES superglobal
    $_FILES['foo'] = array(
      'name' => 'foo.txt',
      'tmp_name' => $this->assetsDirectory . '/foo.txt',
      'error' => 0
    );
  }

  public function testInstantiationWithValidDirectory()
  {
    $storage = new FileSystem($this->uploadDir);
    $this->assertInstanceOf(FileSystem::class, $storage);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testInstantiationWithInvalidDirectory()
  {
    $this->expectException(InvalidArgumentException::class);
    $storage = new FileSystem(__DIR__ . '/test');
  }

  /**
   * Test won't overwrite existing file
   * @expectedException \Almuth\Upload\Exception
   */
  public function testWillNotOverwriteFile()
  {
    $this->expectException(UploadException::class);
    $storage = new FileSystem($this->assetsDirectory, false);
    $storage->upload(new FileInfo(dirname(__DIR__) . '/assets/foo.txt'), 'foo.txt');
  }

  /**
   * Test will overwrite existing file
   */
  public function testWillOverwriteFile()
  {
    $storage = $this->getMockBuilder(FileSystem::class)
      ->enableOriginalConstructor()
      ->setConstructorArgs([$this->assetsDirectory, true])
      ->onlyMethods(['moveUploadedFile'])
      ->getMock();

    $storage->expects($this->any())
      ->method('moveUploadedFile')
      //->with($this->assetsDirectory, true)
      ->willReturn(true);

    $fileInfo = $this->getMockBuilder(FileInfo::class)
      ->enableOriginalConstructor()
      ->setConstructorArgs([dirname(__DIR__) . '/assets/foo.txt', 'foo.txt'])
      ->onlyMethods(['isUploadedFile'])
      ->getMock();

    $fileInfo->expects($this->any())
      ->method('isUploadedFile')
      ->willReturn(true);

    $storage->upload($fileInfo);

    $this->assertEquals('foo.txt', $fileInfo->getNameWithExtension());
  }
}
