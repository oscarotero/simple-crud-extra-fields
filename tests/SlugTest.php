<?php
namespace SimpleCrud\Tests;

use SimpleCrud\SimpleCrud;
use SimpleCrud\Fields\Slug;
use PDO;
use PHPUnit_Framework_TestCase;

class SlugTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $this->db = new SimpleCrud(new PDO('sqlite::memory:'));

        $this->db->executeTransaction(function ($db) {
            $db->execute(
<<<EOT
CREATE TABLE "article" (
    `id`          INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `title`       TEXT,
    `slug`        TEXT
);
EOT
            );
        });
    }

    public function testSlug()
    {
        $db = $this->db;
        Slug::register($db);

        $title = 'Hello world';
        $article = $db->article->create([
            'title' => $title,
            'slug' => $title,
        ]);

        $article->save();

        $this->assertEquals('hello-world', $article->slug);
    }
}
