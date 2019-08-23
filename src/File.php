<?php
declare(strict_types = 1);

namespace SimpleCrud\Fields;

use Psr\Http\Message\UploadedFileInterface;
use SimpleCrud\SimpleCrud;
use SimpleCrud\SimpleCrudException;

/**
 * To store the files in directories and save the filename in the database.
 */
class File extends Field
{
    use SlugifyTrait;

    const CONFIG_UPLOADS_PATH = 'uploads_path';

    protected $config = [
        'root' => null,
        'directory' => null,
        'save_directory' => false,
        'uniq_name' => false
    ];

    protected $relativeDirectory;

    public static function getFactory(): FieldFactory
    {
        return new FieldFactory(self::class, [], ['file', '/[a-z]File$/']);
    }

    public function formatToDatabase($value)
    {
        if ($value instanceof UploadedFileInterface) {
            return $this->upload($value);
        }

        if (empty($value)) {
            return null;
        }

        if (!$this->config['save_directory']) {
            return basename($value);
        }

        return empty($value) ? null : $value;
    }

    public function format($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($this->config['save_directory']) {
            return $value;
        }

        return $this->getDirectory().'/'.$value;
    }

    private function upload(UploadedFileInterface $file): string
    {
        $filename = $this->getFilename($file);
        $root = $this->getRoot();
        $directory = $this->getDirectory().'/';

        if (!is_dir($root.$directory)) {
            mkdir($root.$directory, 0777, true);
        }

        $file->moveTo($root.$directory.$filename);

        if ($this->config['save_directory']) {
            return $directory.$filename;
        }

        return $filename;
    }

    /**
     * Get the name used to save the file in lowercase and without spaces.
     */
    private function getFilename(UploadedFileInterface $file): string
    {
        $name = $file->getClientFilename();

        if ($name === '') {
            return uniqid();
        }

        $info = pathinfo($name);

        if ($this->config['uniq_name']) {
            return uniqid().'.'.$info['extension'];
        }

        return self::slugify($info['filename']).'.'.$info['extension'];
    }

    private function getRoot(): string
    {
        if (!isset($this->config['root'])) {
            $root = $this->table->getDatabase()->getConfig(self::CONFIG_UPLOADS_PATH);

            if (empty($root)) {
                throw new SimpleCrudException('No SimpleCrud\\Fields\\File::CONFIG_UPLOADS_PATH value found to upload files');
            }

            $this->config['root'] = $root;
        }

        return $this->config['root'];
    }

    protected function getDirectory(): string
    {
        if (!isset($this->config['directory'])) {
            $this->config['directory'] = sprintf('/%s/%s', $this->table->getName(), $this->getName());
        }

        return $this->config['directory'];
    }
}
