<?php

use Almuth\Upload\Exception;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Validation\Extension;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
  private $assetsDirectory;

  public function setUp(): void
  {
    $this->assetsDirectory = dirname(__DIR__) . '/assets';
  }

  public function testValidExtension()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Extension('txt');
    $validation->validate($file); // <-- SHOULD NOT throw exception

    $this->expectNotToPerformAssertions();
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testInvalidExtension()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo_wo_ext', 'foo_wo_ext');
    $validation = new Extension('txt');

    $this->expectException(Exception::class);
    $validation->validate($file); // <-- SHOULD throw exception
  }
}
