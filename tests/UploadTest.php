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
        $db->setAttribute(File::ATTR_DIRECTORY, __DIR__.'/tmp');

        $content = 'New file content';
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);

        $file = $db->file->create([
            'name' => 'New file',
            'file' => new UploadedFile($stream, strlen($content), UPLOAD_ERR_OK, ' My  fíle.txt'),
        ]);

        $file->save();
        $fileinfo = $file->file;

        $this->assertTrue($fileinfo->isFile());
        $this->assertInstanceOf('SplFileInfo', $fileinfo);
        $this->assertEquals(__DIR__.'/tmp/file/file/my-file.txt', $fileinfo->getPathname());
        $this->assertEquals($content, $fileinfo->openFile()->fgets());
        $this->assertEquals('my-file.txt', $this->db->execute('SELECT file from file')->fetchColumn(0));

        unlink(__DIR__.'/tmp/file/file/my-file.txt');
        rmdir(__DIR__.'/tmp/file/file');
        rmdir(__DIR__.'/tmp/file');
        rmdir(__DIR__.'/tmp');
    }
}
