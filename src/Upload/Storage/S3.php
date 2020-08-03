<?php
/**
 * Upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     2.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Almuth\Upload\Storage;

use Almuth\Upload\FileInfoInterface;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * FileSystem Storage
 *
 * This class uploads files to a designated directory on the filesystem.
 *
 * @author  Almuth <almuth@nusacart.com>
 * @since   1.4.5
 * @package Upload
 */
class S3 implements \Almuth\Upload\StorageInterface
{
    protected $bucketName;
    protected $client;
    protected $folder;

    /**
     * Constructor
     *
     * @param  array     $s3options [profile,region,version]
     * @param  string    $bucketName S3 bucket name
     * @param  string    $folder folder ini s3 server
     */
    public function __construct(array $s3options, string $bucketName, string $folder = '')
    {
        $this->client = S3Client::factory($s3options);
        $this->bucketName = $bucketName;
        $this->folder = $folder;
    }

    /**
     * Upload
     *
     * @param  \Almuth\Upload\FileInfoInterface     $file The file object to upload
     * @throws AwsException                         If error puting object to s3 server
     */
    public function upload(FileInfoInterface $fileInfo)
    {
        $key  = ($this->folder ? trim($this->folder, '/').'/':'').$fileInfo->getNameWithExtension();
        $file = $fileInfo->getPathname();

        try {
            $result = $this->client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'SourceFile' => $file,
            ]);
        } catch (AwsException $e){
            throw $e;
        }
    }
}