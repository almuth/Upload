<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Almuth\Upload\FileInfo;

class FileInfoTest extends TestCase
{
    protected FileInfo $fileWithExtension;
    protected FileInfo $fileWithoutExtension;

    public function setUp():void
    {
        $this->fileWithExtension = new FileInfo(dirname(__FILE__) . '/assets/foo.txt', 'foo.txt');
        $this->fileWithoutExtension = new FileInfo(dirname(__FILE__) . '/assets/foo_wo_ext', 'foo_wo_ext');
    }

    public function testConstructor()
    {
        $this->assertEquals('foo', $this->fileWithExtension->getName());
        $this->assertEquals('txt', $this->fileWithExtension->getExtension());

        $this->assertEquals('foo_wo_ext', $this->fileWithoutExtension->getName());
        $this->assertEquals('', $this->fileWithoutExtension->getExtension());
    }

    public function testGetName()
    {
        $nameProperty = new \ReflectionProperty($this->fileWithExtension, 'name');
        $nameProperty->setAccessible(true);
        $nameProperty->setValue($this->fileWithExtension, 'bar');

        $this->assertEquals('bar', $this->fileWithExtension->getName());
    }

    public function testSetName()
    {
        $this->fileWithExtension->setName('bar');

        $this->assertEquals('bar', $this->fileWithExtension->getName());
    }

    public function testGetNameWithExtension()
    {
        $this->assertEquals('foo.txt', $this->fileWithExtension->getNameWithExtension());
        $this->assertEquals('foo_wo_ext', $this->fileWithoutExtension->getNameWithExtension());
    }

    public function testGetExtension()
    {
        $this->assertEquals('txt', $this->fileWithExtension->getExtension());
        $this->assertEquals('', $this->fileWithoutExtension->getExtension());
    }

    public function testSetExtension()
    {
        $this->fileWithExtension->setExtension('csv');

        $this->assertEquals('csv', $this->fileWithExtension->getExtension());
    }

    public function testGetMimetype()
    {
        $this->assertEquals('text/plain', $this->fileWithExtension->getMimetype());
    }

    public function testGetMd5()
    {
        $hash = md5_file(dirname(__FILE__) . '/assets/foo.txt');

        $this->assertEquals($hash, $this->fileWithExtension->getMd5());
    }

    public function testGetHash()
    {
        $sha1Hash = hash_file('sha1', dirname(__FILE__) . '/assets/foo.txt');
        $this->assertEquals($sha1Hash, $this->fileWithExtension->getHash('sha1'));

        $md5Hash = hash_file('md5', dirname(__FILE__) . '/assets/foo.txt');

        $this->assertEquals($md5Hash, $this->fileWithExtension->getHash('md5'));
        $this->assertEquals($md5Hash, $this->fileWithExtension->getHash());
    }
}
