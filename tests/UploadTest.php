<?php
namespace SimpleCrud\Tests;

use SimpleCrud\SimpleCrud;
use SimpleCrud\Table;
use SimpleCrud\Fields\File;
use Zend\Diactoros\UploadedFile;
use PDO;
use PHPUnit_Framework_TestCase;

class UploadTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $this->db = new SimpleCrud(new PDO('sqlite::memory:'));

        $this->db->executeTransaction(function ($db) {
            $db->execute(
<<<EOT
CREATE TABLE "file" (
    `id`          INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `name`        TEXT,
    `file`        TEXT
);
EOT
            );
        });
    }

    public function testUpload()
    {
        $db = $this->db;
        File::register($db);
        $dir = __DIR__.'/tmp';
        $db->setAttribute(File::ATTR_DIRECTORY, $dir);

        $content = 'New file content';
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);

        $file = $db->file->create([
            'name' => 'New file',
            'file' => new UploadedFile($stream, strlen($content), UPLOAD_ERR_OK, ' My  fíle.txt'),
        ]);

        $file->save();

        $this->assertTrue(is_file($dir.$file->file));
        $this->assertEquals('/file/file/my-file.txt', $file->file);
        $this->assertEquals($content, file_get_contents($dir.$file->file));
        $this->assertEquals('/file/file/my-file.txt', $this->db->execute('SELECT file from file')->fetchColumn(0));

        unlink(__DIR__.'/tmp/file/file/my-file.txt');
        rmdir(__DIR__.'/tmp/file/file');
        rmdir(__DIR__.'/tmp/file');
        rmdir(__DIR__.'/tmp');
    }
}
