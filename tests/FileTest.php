<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function setUp() : void
    {
        // Set FileInfo factory
        $phpunit = $this;
        \Almuth\Upload\FileInfo::setFactory(function ($tmpName, $name) use ($phpunit) {
            $fileInfo = $phpunit->getMock(
                '\Almuth\Upload\FileInfo',
                array('isUploadedFile'),
                array($tmpName, $name)
            );
            $fileInfo
                ->expects($phpunit->any())
                ->method('isUploadedFile')
                ->will($phpunit->returnValue(true));

            return $fileInfo;
        });

        // Path to test assets
        $this->assetsDirectory = dirname(__FILE__) . '/assets';

        // Mock storage
        $this->storage = $this->getMock(
            '\Almuth\Upload\Storage\FileSystem',
            array('upload'),
            array($this->assetsDirectory)
        );
        $this->storage
            ->expects($this->any())
            ->method('upload')
            ->will($this->returnValue(true));

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
        $file = new \Almuth\Upload\File('multiple', $this->storage);
        $this->assertCount(2, $file);
        $this->assertEquals('foo.txt', $file[0]->getNameWithExtension());
        $this->assertEquals('bar.txt', $file[1]->getNameWithExtension());
    }

    public function testConstructionWithSingleFile()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $this->assertCount(1, $file);
        $this->assertEquals('single.txt', $file[0]->getNameWithExtension());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructionWithInvalidKey()
    {
        $file = new \Almuth\Upload\File('bar', $this->storage);
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

        $callbackBeforeValidate = function (\Almuth\Upload\FileInfoInterface $fileInfo) {
            echo 'BeforeValidate: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackAfterValidate = function (\Almuth\Upload\FileInfoInterface $fileInfo) {
            echo 'AfterValidate: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackBeforeUpload = function (\Almuth\Upload\FileInfoInterface $fileInfo) {
            echo 'BeforeUpload: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackAfterUpload = function (\Almuth\Upload\FileInfoInterface $fileInfo) {
            echo 'AfterUpload: ' . $fileInfo->getName(), PHP_EOL;
        };

        $file = new \Almuth\Upload\File('multiple', $this->storage);
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
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidation(new \Almuth\Upload\Validation\Mimetype(array(
            'text/plain'
        )));
        $this->assertAttributeCount(1, 'validations', $file);
    }

    public function testAddMultipleValidations()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidations(array(
            new \Almuth\Upload\Validation\Mimetype(array(
                'text/plain'
            )),
            new \Almuth\Upload\Validation\Size(50) // minimum bytesize
        ));
        $this->assertAttributeCount(2, 'validations', $file);
    }

    public function testIsValidIfNoValidations()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $this->assertTrue($file->isValid());
    }

    public function testIsValidWithPassingValidations()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidation(new \Almuth\Upload\Validation\Mimetype(array(
            'text/plain'
        )));
        $this->assertTrue($file->isValid());
    }

    public function testIsInvalidWithFailingValidations()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidation(new \Almuth\Upload\Validation\Mimetype(array(
            'text/csv'
        )));
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfHttpErrorCode()
    {
        $file = new \Almuth\Upload\File('bad', $this->storage);
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfNotUploadedFile()
    {
        $phpunit = $this;
        \Almuth\Upload\FileInfo::setFactory(function ($tmpName, $name) use ($phpunit) {
            $fileInfo = $phpunit->getMock(
                '\Almuth\Upload\FileInfo',
                array('isUploadedFile'),
                array($tmpName, $name)
            );
            $fileInfo
                ->expects($phpunit->any())
                ->method('isUploadedFile')
                ->will($phpunit->returnValue(false));

            return $fileInfo;
        });

        $file = new \Almuth\Upload\File('single', $this->storage);
        $this->assertFalse($file->isValid());
    }

    /********************************************************************************
     * Error message tests
     *******************************************************************************/

    public function testPopulatesErrorsIfFailingValidations()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidation(new \Almuth\Upload\Validation\Mimetype(array(
            'text/csv'
        )));
        $file->isValid();
        $this->assertAttributeCount(1, 'errors', $file);
    }

    public function testGetErrors()
    {
        $file = new \Almuth\Upload\File('single', $this->storage);
        $file->addValidation(new \Almuth\Upload\Validation\Mimetype(array(
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
        $file = new \Almuth\Upload\File('single', $this->storage);
        $this->assertTrue($file->isValid());
        $this->assertTrue($file->upload());
    }

    /**
     * @expectedException \Almuth\Upload\Exception
     */
    public function testWillNotUploadIfInvalid()
    {
        $file = new \Almuth\Upload\File('bad', $this->storage);
        $this->assertFalse($file->isValid());
        $file->upload(); // <-- Will throw exception
    }

    /********************************************************************************
     * Helper tests
     *******************************************************************************/

    public function testParsesHumanFriendlyFileSizes()
    {
        $this->assertEquals(100, \Almuth\Upload\File::humanReadableToBytes('100'));
        $this->assertEquals(102400, \Almuth\Upload\File::humanReadableToBytes('100K'));
        $this->assertEquals(104857600, \Almuth\Upload\File::humanReadableToBytes('100M'));
        $this->assertEquals(107374182400, \Almuth\Upload\File::humanReadableToBytes('100G'));
        $this->assertEquals(100, \Almuth\Upload\File::humanReadableToBytes('100F')); // <-- Unrecognized. Assume bytes.
    }
}
