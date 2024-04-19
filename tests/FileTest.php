<?php

declare(strict_types=1);

use Almuth\Upload\Exception as UploadException;
use Almuth\Upload\StorageInterface;
use Almuth\Upload\FileInfoInterface;
use Almuth\Upload\FileInfo;
use Almuth\Upload\File;
use Almuth\Upload\Storage\FileSystem;
use Almuth\Upload\Validation;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
  protected string $assetsDirectory;
  protected string $uploadDir;
  protected StorageInterface $storage;

  public function setUp(): void
  {
    // Set FileInfo factory
    $phpunit = $this;
    FileInfo::setFactory(function ($tmpName, $name) use ($phpunit) {
      $fileInfo = $phpunit->getMockBuilder(FileInfo::class)
        ->enableOriginalConstructor()
        ->setConstructorArgs([$tmpName, $name])
        ->onlyMethods(['isUploadedFile'])
        ->getMock();

      $fileInfo->expects($phpunit->any())
        ->method('isUploadedFile')
        ->willReturn(true);

      return $fileInfo;
    });

    // Path to test assets
    $this->assetsDirectory = dirname(__FILE__) . '/assets';
    $this->uploadDir = $this->assetsDirectory . '/uploads';
    if ( !is_dir($this->uploadDir)){
      mkdir($this->uploadDir, 0777, true);
    }

    foreach(glob($this->uploadDir . '/*') as $uf){
      unlink($uf);
    }

    // Mock storage
    $this->storage = $this->getMockBuilder(FileSystem::class)
      ->enableOriginalConstructor()
      ->setConstructorArgs([$this->uploadDir])
      ->onlyMethods(['upload'])
      ->getMock();
    $this->storage->expects($this->any())
      ->method('upload')
      ->willReturn(true);

    // Prepare uploaded files
    $_FILES['multiple'] = array(
      'name' => array(
        'foo.txt',
        'bar.txt'
      ),
      'tmp_name' => array(
        $this->assetsDirectory . '/foo.txt',
        $this->assetsDirectory . '/bar.txt'
      ),
      'error' => array(
        UPLOAD_ERR_OK,
        UPLOAD_ERR_OK
      )
    );
    $_FILES['single'] = array(
      'name' => 'single.txt',
      'tmp_name' => $this->assetsDirectory . '/single.txt',
      'error' => UPLOAD_ERR_OK
    );
    $_FILES['bad'] = array(
      'name' => 'single.txt',
      'tmp_name' => $this->assetsDirectory . '/single.txt',
      'error' => UPLOAD_ERR_INI_SIZE
    );
  }

  /********************************************************************************
   * Construction tests
   *******************************************************************************/

  public function testConstructionWithMultipleFiles()
  {
    $file = new File('multiple', $this->storage);
    $this->assertCount(2, $file);
    $this->assertEquals('foo.txt', $file[0]->getNameWithExtension());
    $this->assertEquals('bar.txt', $file[1]->getNameWithExtension());
  }

  public function testConstructionWithSingleFile()
  {
    $file = new File('single', $this->storage);
    $this->assertCount(1, $file);
    $this->assertEquals('single.txt', $file[0]->getNameWithExtension());
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testConstructionWithInvalidKey()
  {
    $this->expectException(InvalidArgumentException::class);
    $file = new File('bar', $this->storage);
  }

  /********************************************************************************
   * Callback tests
   *******************************************************************************/

  /**
   * Test callbacks
   *
   * This test will make sure callbacks are called for each FileInfoInterface
   * object in the correct order.
   */
  public function testCallbacks()
  {
    $this->expectOutputString("BeforeValidate: foo\nAfterValidate: foo\nBeforeValidate: bar\nAfterValidate: bar\nBeforeUpload: foo\nAfterUpload: foo\nBeforeUpload: bar\nAfterUpload: bar\n");

    $callbackBeforeValidate = function (FileInfoInterface $fileInfo) {
      echo "BeforeValidate: " . $fileInfo->getName() . "\n";
    };

    $callbackAfterValidate = function (FileInfoInterface $fileInfo) {
      echo "AfterValidate: " . $fileInfo->getName() . "\n";
    };

    $callbackBeforeUpload = function (FileInfoInterface $fileInfo) {
      echo "BeforeUpload: " . $fileInfo->getName() . "\n";
    };

    $callbackAfterUpload = function (FileInfoInterface $fileInfo) {
      echo "AfterUpload: " . $fileInfo->getName() . "\n";
    };

    $file = new File('multiple', $this->storage);
    $file->beforeValidate($callbackBeforeValidate);
    $file->afterValidate($callbackAfterValidate);
    $file->beforeUpload($callbackBeforeUpload);
    $file->afterUpload($callbackAfterUpload);
    $file->upload();
  }

  /********************************************************************************
   * Validation tests
   *******************************************************************************/

  public function testAddSingleValidation()
  {
    $file = new File('single', $this->storage);
    $file->addValidation(new Validation\Mimetype(array(
      'text/plain'
    )));
    $this->assertCount(1, $file->getValidations());
  }

  public function testAddMultipleValidations()
  {
    $file = new File('single', $this->storage);
    $file->addValidations(array(
      new Validation\Mimetype(array(
        'text/plain'
      )),
      new Validation\Size(50) // minimum bytesize
    ));
    $this->assertCount(2, $file->getValidations());
  }

  public function testIsValidIfNoValidations()
  {
    $file = new File('single', $this->storage);
    $this->assertTrue($file->isValid());
  }

  public function testIsValidWithPassingValidations()
  {
    $file = new File('single', $this->storage);
    $file->addValidation(new Validation\Mimetype(array(
      'text/plain'
    )));
    $this->assertTrue($file->isValid());
  }

  public function testIsInvalidWithFailingValidations()
  {
    $file = new File('single', $this->storage);
    $file->addValidation(new Validation\Mimetype(array(
      'text/csv'
    )));
    $this->assertFalse($file->isValid());
  }

  public function testIsInvalidIfHttpErrorCode()
  {
    $file = new File('bad', $this->storage);
    $this->assertFalse($file->isValid());
  }

  public function testIsInvalidIfNotUploadedFile()
  {
    $phpunit = $this;
    FileInfo::setFactory(function ($tmpName, $name) use ($phpunit) {
      $fileInfo = $phpunit->getMockBuilder(FileInfo::class)
        ->enableOriginalConstructor()
        ->setConstructorArgs([$tmpName, $name])
        ->onlyMethods(['isUploadedFile'])
        ->getMock();

      $fileInfo->method('isUploadedFile')
        ->willReturn(false);

      return $fileInfo;
    });

    $file = new File('single', $this->storage);
    $this->assertFalse($file->isValid());
  }

  /********************************************************************************
   * Error message tests
   *******************************************************************************/

  public function testPopulatesErrorsIfFailingValidations()
  {
    $file = new File('single', $this->storage);
    $file->addValidation(new Validation\Mimetype(array(
      'text/csv'
    )));
    $file->isValid();
    $this->assertCount(1, $file->getErrors());
  }

  public function testGetErrors()
  {
    $file = new File('single', $this->storage);
    $file->addValidation(new Validation\Mimetype(array(
      'text/csv'
    )));
    $file->isValid();
    $this->assertCount(1, $file->getErrors());
  }

  /********************************************************************************
   * Upload tests
   *******************************************************************************/

  public function testWillUploadIfValid()
  {
    $file = new File('single', $this->storage);
    $this->assertTrue($file->isValid());
    $this->assertTrue($file->upload());
  }

  /**
   * @expectedException Exception
   */
  public function testWillNotUploadIfInvalid()
  {
    $file = new File('bad', $this->storage);
    $this->assertFalse($file->isValid());
    $this->expectException(UploadException::class);
    $file->upload(); // <-- Will throw exception
  }

  /********************************************************************************
   * Helper tests
   *******************************************************************************/

  public function testParsesHumanFriendlyFileSizes()
  {
    $this->assertEquals(100, File::humanReadableToBytes('100'));
    $this->assertEquals(102400, File::humanReadableToBytes('100K'));
    $this->assertEquals(104857600, File::humanReadableToBytes('100M'));
    $this->assertEquals(107374182400, File::humanReadableToBytes('100G'));
    $this->assertEquals(100, File::humanReadableToBytes('100F')); // <-- Unrecognized. Assume bytes.
  }
}
