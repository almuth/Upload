<?php

use Almuth\Upload\Exception as UploadException;
use Almuth\Upload\FileInfo;
use Almuth\Upload\Storage\S3 as S3Storage;
use PHPUnit\Framework\TestCase;

class S3Test extends TestCase
{
  private $options;
  /**
   * Setup (each test)
   */
  public function setUp(): void
  {
    // Path to test assets
    $this->options = include dirname(__DIR__) . '/s3options.php';
  }

  public function testWillPutObject()
  {
    $s3Storage = new S3Storage($this->options['client'], $this->options['bucket_name'], $this->options['folder']);
    $file = new FileInfo(dirname(__DIR__) . '/assets/foo.png', 'foo-test.png');

    $s3Storage->upload($file);

    $this->expectNotToPerformAssertions();
  }

  public function testFailPutObject()
  {
    $s3Storage = new S3Storage($this->options['client'], 'test-bucket-axcgd', $this->options['folder']);
    $file = new FileInfo(dirname(__DIR__) . '/assets/foo.png', 'foo-test.png');

    $this->expectException(UploadException::class);
    $s3Storage->upload($file);
  }
}
