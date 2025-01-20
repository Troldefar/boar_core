<?php

namespace app\core\src;

use \app\core\src\miscellaneous\CoreFunctions;
use \app\core\src\utilities\Image;
use \app\core\src\traits\file\FileStaticMethodTrait;

final class File {

    use FileStaticMethodTrait;

    public const PLACEHOLDER_FILE = '/usr/www/users/appkat/dev/public/resources/images/placeholder.jpg';
    public const INVALID_EXTENSION  = 'Invalid file extension';
    public const INVALID_FILE_NAME  = 'Invalid file name';
    public const INVALID_FILE_SIZE  = 'File is to big';
    public const NO_FILES_ATTACHED = 'No files attached';
    public const VIEWS_FOLDER       = '/views/';
    public const LAYOUTS_FOLDER     = '/views/layouts/';
    public const JS_EXTENSION = 'js';
    public const CSS_EXTENSION = 'css';
    public const TPL_FILE_EXTENSION = '.tpl.php';
    public const PHP_EXTENSION = '.php';

    public const FILE_NOT_FOUND     = 'File not found';
    protected const MAXIMUM_FILE_SIZE  = 10000000;

    public function __construct(
        protected $file,
        protected $fileDirectory = null
    ) {
        if (is_string($file)) $this->adjustFile();
        $this->file = (object)$this->file;
        $this->validateDirectory();
    }

    private function validateDirectory() {
        $this->fileDirectory ??= dirname(__DIR__, 2).'/uploads';
    }

    public function adjustFile() {
        $fileName = $this->file;
        $this->file = [
            'name' => $fileName,
            'size' => 0,
            'type' => CoreFunctions::last(explode('.', $fileName))->scalar
        ];
    }

    public function getDirectory(): string {
        return $this->fileDirectory;
    }

    public function getName(): string {
        return str_replace(' ', '', $this->file->name);
    }

    public function setName(string $name): void {
        $this->file->name = $name;
    }

    public function getTmpName(): string {
        return $this->file->tmp_name;
    }

    public function getFileSize(): string {
        return $this->file->size;
    }

    public function getFileType(): string {
        return $this->file->type;
    }

    public function getFullFilePath(): string {
        return $this->getDirectory() . $this->getName();
    }

    public function generateFinalName(): string {
        return $this->getDirectory() . '/' . strtotime('now') . rand(1, 20000) . '-' . $this->getName();
    }

    public function moveFile(): string {
        $this->validateFileConditions();
        $destination = $this->generateFinalName();
        move_uploaded_file($this->getTmpName(), $destination);
        $this->resizeImage(new Image($destination));
        return $destination;
    }

    private function resizeImage(Image $cImage) {
        $cImage->resizeImage();
    }

    public function validateFileConditions() {
        if (!$this->checkFileType()) throw new \Exception(self::INVALID_EXTENSION);
        if (!$this->validateFileName()) throw new \Exception(self::INVALID_FILE_NAME);
        if (!$this->validateSize()) throw new \Exception(self::INVALID_FILE_SIZE);
    }

    public function validateSize(): bool {
        return $this->getFileSize() < self::MAXIMUM_FILE_SIZE;
    }

    protected function checkFileType(): bool {
        $fileType = preg_replace('~.*' . preg_quote('/', '~') . '~', '', $this->getFileType());
        return in_array($fileType, app()->getConfig()->get('fileHandling')->allowedFileTypes);
    }

    public function validateFileName(): bool {
        $fileName = preg_replace('~\..*~', '', $this->getName());
        return preg_match('/[a-zA-Z0-9]/', $fileName);
    }

    public function unlink(): bool {
        if (!$this->exists()) return false;
        return unlink($this->getFilePath());
    }

    public function getFile(): string|bool {
        if (!$this->exists()) return self::FILE_NOT_FOUND;
        return file_get_contents($this->getFilePath());
    }

    public function getFilePath() {
        return $this->getDirectory() .'/'. $this->getName();
    }

    public function exists() {
        return file_exists($this->getFilePath());
    }

}