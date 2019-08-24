<?php
declare(strict_types = 1);

namespace SimpleCrud\Fields;

use Cocur\Slugify\Slugify;

/**
 * To slugify values.
 */
trait SlugifyTrait
{
    private static $slugify;

    private static function slugify(string $text): string
    {
        if (self::$slugify === null) {
            self::$slugify = new Slugify();
        }

        return self::$slugify->slugify($text);
    }
}
