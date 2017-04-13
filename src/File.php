<?php

namespace SimpleCrud\Fields;

use Psr\Http\Message\UploadedFileInterface;
use SimpleCrud\SimpleCrud;
use SimpleCrud\SimpleCrudException;
use SplFileInfo;

/**
 * To save files.
 */
class File extends Field
{
    use SlugifyTrait;

    const ATTR_DIRECTORY = 'simplecrud.file.directory';
    const ATTR_SAVE_RELATIVE_DIRECTORY = 'simplecrud.file.save_relative_directory';

    protected $directory;

    public static function register(SimpleCrud $simpleCrud)
    {
        $fieldFactory = $simpleCrud->getFieldFactory();
        $fieldFactory->mapNames(['file' => 'File']);
        $fieldFactory->mapRegex(['/[a-z]File$/' => 'File']);
    }

    /**
     * {@inheritdoc}
     */
    public function dataToDatabase($data)
    {
        if ($data instanceof UploadedFileInterface) {
            return $this->upload($data);
        }

        if ($data instanceof SplFileInfo) {
            return $data->getFilename();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function dataFromDatabase($data)
    {
        if (!empty($data)) {
            return new SplFileInfo($this->getDirectory().$this->getRelativeDirectory().$data);
        }

        return $data;
    }

    /**
     * Upload the file and return the value.
     * 
     * @param UploadedFileInterface $file
     * 
     * @return string
     */
    private function upload(UploadedFileInterface $file)
    {
        $filename = $this->getFilename($file);
        $root = $this->getDirectory();
        $relative = $this->getRelativeDirectory();

        if (!is_dir($root.$relative)) {
            mkdir($root.$relative, 0777, true);
        }

        if ($this->table->getDatabase()->getAttribute(self::ATTR_SAVE_RELATIVE_DIRECTORY)) {
            $filename = $relative.$filename;
        } else {
            $root .= $relative;
        }

        $file->moveTo($root.$filename);

        return $filename;
    }

    /**
     * Get the name used to save the file in lowercase and without spaces.
     * 
     * @param UploadedFilenameInterface $file
     * 
     * @return string
     */
    private function getFilename(UploadedFileInterface $file)
    {
        $name = $file->getClientFilename();

        if ($name === '') {
            return uniqid();
        }

        $info = pathinfo($name);

        return self::slugify($info['filename']).'.'.$info['extension'];
    }

    /**
     * Get the directory where the file will be saved.
     * 
     * @return string
     */
    private function getDirectory()
    {
        if ($this->directory === null) {
            $this->directory = $this->table->getDatabase()->getAttribute(self::ATTR_DIRECTORY);

            if (empty($this->directory)) {
                throw new SimpleCrudException('No SimpleCrud\\Fields\\File::ATTR_DIRECTORY attribute found to upload files');
            }
        }

        return $this->directory;
    }

    /**
     * Get the relative where the file will be saved.
     * 
     * @return string
     */
    protected function getRelativeDirectory()
    {
        return "/{$this->table->name}/{$this->name}/";
    }
}
