<?php

use Almuth\Upload\Exception;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Validation\Dimensions;
use PHPUnit\Framework\TestCase;

class DimensionsTest extends TestCase
{
  private $assetsDirectory;

  public function setUp(): void
  {
    $this->assetsDirectory = dirname(__DIR__) . '/assets';
  }

  public function testWidthAndHeight()
  {
    $dimensions = new Dimensions(100, 100);
    $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
    $dimensions->validate($file);

    $this->expectNotToPerformAssertions();
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testWidthDoesntMatch()
  {
    $dimensions = new Dimensions(200, 100);
    $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');

    $this->expectException(Exception::class);
    $dimensions->validate($file);
  }

  /**
   * @expectedException \Almuth\Upload\Exception
   */
  public function testHeightDoesntMatch()
  {
    $dimensions = new Dimensions(100, 200);
    $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');

    $this->expectException(Exception::class);
    $dimensions->validate($file);
  }
}
