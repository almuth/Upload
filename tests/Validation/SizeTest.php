<?php

use Almuth\Upload\Exception;
use Almuth\Upload\File;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Validation\Size;
use PHPUnit\Framework\TestCase;

class SizeTest extends TestCase
{
  private $assetsDirectory;

  public function setUp(): void
  {
    $this->assetsDirectory = dirname(__DIR__) . '/assets';
  }

  public function testValidFileSize()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Size(500);
    $validation->validate($file); // <-- SHOULD NOT throw exception

    $this->expectNotToPerformAssertions();
  }

  public function testValidFileSizeWithHumanReadableArgument()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Size('500B');
    $validation->validate($file); // <-- SHOULD NOT throw exception

    $this->expectNotToPerformAssertions();
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testInvalidFileSize()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Size(400);

    $this->expectException(Exception::class);
    $validation->validate($file); // <-- SHOULD throw exception
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testInvalidFileSizeWithHumanReadableArgument()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Size('400B');

    $this->expectException(Exception::class);
    $validation->validate($file); // <-- SHOULD throw exception
  }
}
