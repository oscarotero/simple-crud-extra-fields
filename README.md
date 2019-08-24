# SimpleCrud Fields

Package with extra fields for [simple-crud](https://github.com/oscarotero/simple-crud)

## Installation

This package is installable and autoloadable via Composer as [simple-crud/extra-fields](https://packagist.org/packages/simple-crud/extra-fields).

```
$ composer require simple-crud/extra-fields
```

### File

Used to upload files into a directory and save the file name in the database. Detects instances of `Psr\Http\Message\UploadedFileInterface` [see here PSR-7 standard](http://www.php-fig.org/psr/psr-7/).

* First, you must define the uploads path used by the database, using the attribute `SimpleCrud::ATTR_UPLOADS`
* On register the field, the `File` format will be asigned to any field named "file" or ending by "File" (for example: imageFile, avatarFile, etc)
* By default, the file is saved in a subdirectory named as `[table]/[field]`. For example, the images of the field `avatar` of the table `user` will be saved in the folder `uploads/user/avatar`.
* The filename is slugified and converted to lowercase. For example, the file `My Picture.JPG` is renamed to `my-picture.jpg`.

```php
use SimpleCrud\Fields\File;

//Register the field
File::register($simpleCrud);

//Configure the directory to upload the files
$simpleCrud->setConfig(File::CONFIG_UPLOADS_PATH, '/path/to/uploads');

//You can also configure the File field
$simpleCrud->user->file
	->setConfig('directory', '/path/to/uploads') //custom directory used instead the default File::DIRECTORY
	->setConfig('relative_directory', '/images') //custom subdirectory (by default is /{table_name}/{field_name})
	->setConfig('save_relative_directory', true) //whether save the relative_directory in the database instead only the filename (false by default)

//Get the data from the serverRequest
$data = $request->getParsedBody();
$files = $request->getUploadedFiles();

//Create the new user
$user = $simpleCrud->user->create([
    'name' => $data['name'],
    'email' => $data['email'],
    'file' => $files['avatar'],
]);

//Save the data
$user->save();

//Get the avatar file
echo $user->file; // /user/avatar/image.jpg;
```

### Slug

Used to save slugified values using [cocur/slugify](https://github.com/cocur/slugify). On register the field, the `Slug` format will be asigned to any field named "slug"

```php
use SimpleCrud\Fields\Slug;

//Register the field
Slug::register($simpleCrud);

//Create the new article
$title = 'Hello world'
$article = $simpleCrud->articles->create([
    'title' => $title,
    'slug' => $title,
]);

//Save the data
$article->save();

//Get the slug
echo $user->article->slug // hello-world
```