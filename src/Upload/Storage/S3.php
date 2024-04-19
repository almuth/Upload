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

use Almuth\Upload\Exception;
use Almuth\Upload\FileInfoInterface;
use Almuth\Upload\StorageInterface;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;

/**
 * FileSystem Storage
 *
 * This class uploads files to a designated directory on the filesystem.
 *
 * @author  Almuth <almuth@perigi.my.id>
 * @since   1.4.5
 * @package Upload
 */
class S3 implements StorageInterface
{
  protected string $bucketName;
  protected S3ClientInterface $client;
  protected string $folder;
  protected string $acl;

  /**
   * Constructor
   *
   * @param  array     $s3ClientOptions [profile,region,version]
   * @param  string    $bucketName S3 bucket name
   * @param  string    $folder folder in s3 server
   * @param  string    $acl object acl
   */
  public function __construct(array $s3ClientOptions, string $bucketName, string $folder = '', string $acl = 'public-read')
  {
    $this->client = new S3Client($s3ClientOptions);
    $this->bucketName = $bucketName;
    $this->folder = $folder;
    $this->acl = $acl ?: 'public-read';
  }

  /**
   * Upload
   *
   * @param  FileInfoInterface     $file The file object to upload
   * @throws Exception          If error puting object to s3 server
   */
  public function upload(FileInfoInterface $fileInfo)
  {
    $key  = ($this->folder ? trim($this->folder, '/') . '/' : '') . $fileInfo->getNameWithExtension();
    $file = $fileInfo->getPathname();

    try {
      $this->client->putObject([
        'Bucket' => $this->bucketName,
        'Key' => $key,
        'SourceFile' => $file,
        'ACL' => $this->acl
      ]);
    } catch (S3Exception $e) {
      throw new Exception($e->getMessage(), $fileInfo);
    }
  }
}
