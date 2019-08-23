<?php
namespace SimpleCrud\Tests;

use SimpleCrud\Database;
use SimpleCrud\Fields\Slug;
use PDO;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    private $db;

    public function setUp(): void
    {
        $this->db = new Database(new PDO('sqlite::memory:'));

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
        $db->setFieldFactory(Slug::getFactory());

        $title = 'Hello world';
        $article = $db->article->create([
            'title' => $title,
            'slug' => $title,
        ]);

        $article->save()->reload();

        $this->assertEquals('hello-world', $article->slug);
    }
}
