<?php

namespace SimpleCrud\Fields;

use Cocur\Slugify\Slugify;

/**
 * To slugify values.
 */
trait SlugifyTrait
{
	private static $slugify;

    private static function slugify($text)
    {
        if (self::$slugify === null) {
            self::$slugify = new Slugify();
        }

        return self::$slugify->slugify($text);
    }
}
