<?php
namespace Almuth\Upload;

class Exception extends \RuntimeException
{
    /**
     * @var \Almuth\Upload\FileInfoInterface
     */
    protected $fileInfo;

    /**
     * Constructor
     *
     * @param string                    $message  The Exception message
     * @param \Almuth\Upload\FileInfoInterface $fileInfo The related file instance
     */
    public function __construct($message, FileInfoInterface $fileInfo = null)
    {
        $this->fileInfo = $fileInfo;

        parent::__construct($message);
    }

    /**
     * Get related file
     *
     * @return \Almuth\Upload\FileInfoInterface
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }
}
