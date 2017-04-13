<?php

namespace SimpleCrud\Fields;

use Psr\Http\Message\UploadedFileInterface;
use SimpleCrud\SimpleCrud;
use SimpleCrud\SimpleCrudException;

/**
 * To save files.
 */
class File extends Field
{
    use SlugifyTrait;

    const ATTR_DIRECTORY = 'simplecrud.file.directory';

    protected $directory;
    protected $relativeDirectory;

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

        return empty($data) ? null: $data;
    }

    /**
     * {@inheritdoc}
     */
    public function dataFromDatabase($data)
    {
        if (!empty($data)) {
            return $this->getRelativeDirectory().$data;
        }

        return null;
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
        if (!isset($this->config['directory'])) {
            $directory = $this->table->getDatabase()->getAttribute(self::ATTR_DIRECTORY);

            if (empty($directory)) {
                throw new SimpleCrudException('No SimpleCrud\\Fields\\File::ATTR_DIRECTORY attribute found to upload files');
            }

            $this->config['directory'] = $directory;
        }

        return $this->config['directory'];
    }

    /**
     * Get the relative where the file will be saved.
     * 
     * @return string
     */
    protected function getRelativeDirectory()
    {
        if (!isset($this->config['relative_directory'])) {
            $this->config['relative_directory'] = "/{$this->table->name}/{$this->name}/";
        }

        return $this->config['relative_directory'];
    }
}
