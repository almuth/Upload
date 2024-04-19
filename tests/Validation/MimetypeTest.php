<?php

use Almuth\Upload\Exception;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Validation\Mimetype;
use PHPUnit\Framework\TestCase;

class MimetypeTest extends TestCase
{
  private $assetsDirectory;

  public function setUp(): void
  {
    $this->assetsDirectory = dirname(__DIR__) . '/assets';
  }

  public function testValidMimetype()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Mimetype(array('text/plain'));
    $validation->validate($file); // <-- SHOULD NOT throw exception

    $this->expectNotToPerformAssertions();
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testInvalidMimetype()
  {
    $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
    $validation = new Mimetype(array('image/png'));

    $this->expectException(Exception::class);
    $validation->validate($file); // <-- SHOULD throw exception
  }
}
