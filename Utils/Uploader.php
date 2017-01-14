<?php

namespace MediaLibraryBundle\Utils;

use MediaLibraryBundle\Image\Uploadable;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class Uploader {

    /**
     * @var array
     */
    public $allowedTypes = array(
        'jpg',
        'jpeg',
        'png',
    );

    /**
     * @var
     */
    private $fileSystem;

    /**
     * @var
     */
    private $file;

    /**
     * @var
     */
    private $uploadOk = FALSE;

    /**
     * Uploader constructor.
     */
    public function __construct()
    {
        // $this->fileSystem = $system;
    }

    public function setFileSystem($fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sanitizes and cleans up filename
     *
     * @param $str
     * @return mixed
     */
    private function createFilename($str) {
        // Sanitize and transliterate
        $str = strtolower($str);
        $str = strip_tags($str);
        $safeName = preg_replace('/[^a-z0-9-_\.]/','', $str);

        // Create unique filename.
        $i = 1;
        while (!$this->isUniqueFilename($safeName)) {
            $nameArray = explode('.', $safeName);
            $safeName = $nameArray[0] . '-' . $i . '.' . $nameArray[1];
            $i++;
        }

        // Prepend the filesystem path.
        // @TODO: Get the path prefix from the fileSystem object.
        // dump($this->fileSystem);

        return $safeName;
    }

    /**
     * Convert MB/K/G to bytesize
     *
     * @param $uploadMaxSize
     * @return int
     */
    public function mToBytes($uploadMaxSize) {
        $uploadMaxSize = trim($uploadMaxSize);
        $last = strtolower($uploadMaxSize[strlen($uploadMaxSize) - 1]);

        switch($last) {
            case 'g':
                $uploadMaxSize *= 1024 * 1000 * 1000;
                break;

            case 'm':
                $uploadMaxSize *= 1024 * 1000;
                break;

            case 'k':
                $uploadMaxSize *= 1024;
                break;
        }

        return $uploadMaxSize;
    }

    /**
     * Checks to see if a file name is unique in the storage directory.
     *
     * @param $name
     * @return bool
     */
    private function isUniqueFilename($name) {
        if ($this->fileSystem->has($name)) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    /**
     * Tests if mime type is allowed.
     *
     * @return bool
     */
    public function isAllowedType() {
        $extension = $this->getFile()->guessExtension();
        if (in_array($extension, $this->allowedTypes)) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    /**
     * After uploading, this function checks if the image is valid and if so moves it to an appropriate storage location.
     *
     * @param Uploadable $image.
     * @return Uploadable
     */
    public function upload(Uploadable $image)
    {
        // The file property can be empty if the field is not required.
        if (null === $image->getFile()) {
            return FALSE;
        }

        $this->setFile($image->getFile());
        $messages = array();
        $this->uploadOk = TRUE;

        // Get max file upload in bytes.
        $uploadMaxSize = ini_get('upload_max_filesize');
        $uploadMaxSize = $this->mToBytes($uploadMaxSize);

        if (!$this->file instanceof UploadedFile && !empty($image->getFile()->getError())) {
            $messages[] = 'Uploaded file should be an instance of \'UploadedFile\'';
            $this->uploadOk = FALSE;
        }
        elseif ($this->file->getSize() > $uploadMaxSize) {
            $messages[] = sprintf('%s: File size cannot be larger than %s', $this->file->getSize(), $uploadMaxSize);
            $this->uploadOk = FALSE;
        }
        elseif (!$this->isAllowedType()) {
            $messages[] = 'File type is not allowed';
            $this->uploadOk = FALSE;
        }
        else {

            // $file = $request->files->get($uploadname);

            if ($this->file->isValid()) {

                $fileName = $this->file->getClientOriginalName();
                $newFileName = $this->createFilename($fileName);

                $stream = fopen($this->file->getRealPath(), 'r+');
                $this->fileSystem->writeStream($newFileName, $stream);
                fclose($stream);

                // $info = $this->filesystem->getWithMetadata('images/' . $newFileName, ['timestamp', 'mimetype']);


                // Set the path property to the filename where you've saved the file.
                $image->setPath($newFileName);
                $this->uploadOk = TRUE;

                // Set the image metadata.
                // $fileSize = $this->fileSystem->getSize($newFileName);
                $realPath = $this->fileSystem->getAdapter()->applyPathPrefix($fileName);
                $imageData = getimagesize($realPath);
                $image->setWidth($imageData[0]);
                $image->setHeight($imageData[1]);

                // Clean up the file property as you won't need it anymore.
                $this->file = null;
                $image->setFile(null);
            }
        }

        if ($this->uploadOk) {
            return $image;
        }
        else {
            throw new FileException($messages[0]);
        }
    }
}
