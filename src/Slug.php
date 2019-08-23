<?php
declare(strict_types = 1);

namespace SimpleCrud\Fields;

use Cocur\Slugify\Slugify;

/**
 * To slugify values before save.
 */
class Slug extends Field
{
    use SlugifyTrait;

    public static function getFactory(): FieldFactory
    {
        return new FieldFactory(self::class, [], ['slug']);
    }

    protected function formatToDatabase($value)
    {
        return self::slugify($value);
    }
}
