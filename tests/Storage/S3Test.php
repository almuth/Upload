<?php

use PHPUnit\Framework\TestCase;

class S3Test extends TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp():void
    {
        // Path to test assets
        $this->options = include dirname(__DIR__) . '/s3options.php';
    }

    public function testWillPutObject(){
        $s3Storage = new \Almuth\Upload\Storage\S3($this->options['client'], $this->options['bucket_name'], $this->options['folder']);
        $file = new \Almuth\Upload\FileInfo(dirname(__DIR__) .'/assets/foo.png', 'foo-test.png');

        $s3Storage->upload($file);
    }
}