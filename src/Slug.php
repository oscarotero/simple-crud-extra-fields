<?php

namespace SimpleCrud\Fields;

use Cocur\Slugify\Slugify;
use SimpleCrud\SimpleCrud;

/**
 * To slugify values before save.
 */
class Slug extends Field
{
    use SlugifyTrait;

	public static function register(SimpleCrud $simpleCrud)
    {
        $fieldFactory = $simpleCrud->getFieldFactory();
        $fieldFactory->mapNames(['slug' => 'Slug']);
    }

    /**
     * {@inheritdoc}
     */
    public function dataToDatabase($data)
    {
        return self::slugify($data);
    }
}
