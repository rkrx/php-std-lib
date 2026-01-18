# File System

## CSV Handling

### StdLib\FS\CSVFile::getCSVLinesFromFile

Examples:

```php
use StdLib\FS\CSVFile;

foreach (CSVFile::getCSVLinesFromFile('data.csv') as $row) {
	var_dump($row);
}
```

```php
use StdLib\FS\CSVFile;
use SplFileInfo;

$file = new SplFileInfo('/tmp/report.csv');
foreach (CSVFile::getCSVLinesFromFile($file, ';', '"', false) as $row) {
	echo $row[0] . PHP_EOL;
}
```

Description:

Reads a CSV file as a generator. When `$header` is enabled, the first row is used as keys and each subsequent row is returned as an associative array. When `$header` is disabled, each row is returned as a numeric array. A UTF-8 BOM in header columns is stripped automatically.

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the CSV file or an `SplFileInfo` instance.
- `string $separator`  
  Field delimiter used in the CSV file.
- `string $enclosure`  
  Field enclosure character used in the CSV file.
- `bool $header`  
  Whether the first row should be treated as the header.

Return Values:

Returns a `Generator` that yields either `array<string, string|null>` when `$header` is `true`, or `string[]` when `$header` is `false`.

Throws:

`RuntimeException` if the file cannot be opened or the header row cannot be read.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`fgetcsv`](https://www.php.net/manual/en/function.fgetcsv.php)
- [`array_map`](https://www.php.net/manual/en/function.array-map.php)
- [`strtr`](https://www.php.net/manual/en/function.strtr.php)
- [`count`](https://www.php.net/manual/en/function.count.php)
- [`feof`](https://www.php.net/manual/en/function.feof.php)
- [`array_slice`](https://www.php.net/manual/en/function.array-slice.php)
- [`array_combine`](https://www.php.net/manual/en/function.array-combine.php)
- [`fclose`](https://www.php.net/manual/en/function.fclose.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\CSVFile::writeCSVFile

Examples:

```php
use StdLib\FS\CSVFile;

$rows = [
	['id' => 1, 'name' => 'Ada'],
	['id' => 2, 'name' => 'Linus'],
];

CSVFile::writeCSVFile($rows, 'users.csv', ',');
```

```php
use StdLib\FS\CSVFile;

$rows = [
	['alpha', 'beta'],
	['gamma', 'delta'],
];

CSVFile::writeCSVFile($rows, '/tmp/no-header.csv', ';', false);
```

Description:

Writes iterable CSV rows to a file. When `$useHeader` is enabled, the keys from the first row are written as the header before writing the remaining rows.

Parameters:

- `iterable<string[]> $lines`  
  Rows to write to the CSV file.
- `string $filepath`  
  Destination file path.
- `string $separator`  
  Field delimiter to write.
- `bool $useHeader`  
  Whether to write the header row from the first line keys.

Return Values:

Returns `void`.

Throws:

`RuntimeException` if the file cannot be opened.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`fputcsv`](https://www.php.net/manual/en/function.fputcsv.php)
- [`array_keys`](https://www.php.net/manual/en/function.array-keys.php)
- [`fclose`](https://www.php.net/manual/en/function.fclose.php)

## Directory Handling

### StdLib\FS\Directory::allItemsInDirectory

Examples:

```php
use StdLib\FS\Directory;

foreach (Directory::allItemsInDirectory('/var/log') as $item) {
	echo $item->getFilename() . PHP_EOL;
}
```

```php
use StdLib\FS\Directory;
use SplFileInfo;

$filter = static fn(SplFileInfo $item) => $item->isFile();
foreach (Directory::allItemsInDirectory('/var/log', true, $filter) as $item) {
	echo $item->getPathname() . PHP_EOL;
}
```

Description:

Returns an iterator over items in a directory. When `$recursive` is enabled, it traverses the directory tree in depth-first order. When `$filter` is provided, only items for which the filter returns `true` are yielded.

Parameters:

- `string $directory`  
  Directory path to list.
- `bool $recursive`  
  Whether to traverse subdirectories.
- `null|callable(SplFileInfo):bool $filter`  
  Optional filter callback. Return `true` to include the item.

Return Values:

Returns an `Iterator` that yields `SplFileInfo` instances.

Throws:

None directly. Exceptions from SPL iterators may bubble up if the directory is invalid or unreadable.

See Also:

- [`FilesystemIterator`](https://www.php.net/manual/en/class.filesystemiterator.php)
- [`RecursiveDirectoryIterator`](https://www.php.net/manual/en/class.recursivedirectoryiterator.php)
- [`RecursiveIteratorIterator`](https://www.php.net/manual/en/class.recursiveiteratoriterator.php)
- [`CallbackFilterIterator`](https://www.php.net/manual/en/class.callbackfilteriterator.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\Directory::readLinesFromFile

Examples:

```php
use StdLib\FS\Directory;

foreach (Directory::readLinesFromFile('/etc/hosts') as $line) {
	echo $line . PHP_EOL;
}
```

Description:

Reads a file line-by-line as a generator. Each yielded line is trimmed with `rtrim()`, which removes trailing whitespace including line endings.

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the file or an `SplFileInfo` instance.

Return Values:

Returns a `Generator` that yields strings.

Throws:

`RuntimeException` if the file cannot be opened.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`feof`](https://www.php.net/manual/en/function.feof.php)
- [`fgets`](https://www.php.net/manual/en/function.fgets.php)
- [`rtrim`](https://www.php.net/manual/en/function.rtrim.php)
- [`fclose`](https://www.php.net/manual/en/function.fclose.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\Directory::mkdir

Examples:

```php
use StdLib\FS\Directory;

Directory::mkdir('/tmp/app/cache');
```

Description:

Ensures a directory exists. Creates the directory recursively with mode `0777` when needed. If the path already exists, it returns `true`.

Parameters:

- `string $string`  
  Directory path to create.

Return Values:

Returns `true` when the directory exists or is created.

Throws:

`RuntimeException` if the directory cannot be created.

See Also:

- [`file_exists`](https://www.php.net/manual/en/function.file-exists.php)
- [`mkdir`](https://www.php.net/manual/en/function.mkdir.php)
- [`is_dir`](https://www.php.net/manual/en/function.is-dir.php)
- [`sprintf`](https://www.php.net/manual/en/function.sprintf.php)

### StdLib\FS\Directory::getCSVLinesFromString

Examples:

```php
use StdLib\FS\Directory;

$csv = "id,name\n1,Ada\n2,Linus\n";
foreach (Directory::getCSVLinesFromString($csv, ',', true) as $row) {
	var_dump($row);
}
```

```php
use StdLib\FS\Directory;

$csv = "alpha;beta\ngamma;delta\n";
foreach (Directory::getCSVLinesFromString($csv, ';', false) as $row) {
	echo $row[0] . PHP_EOL;
}
```

Description:

Parses CSV content from a string using an in-memory stream. When `$header` is enabled, the first row is used as keys and each subsequent row is returned as an associative array. Header columns are used as-is (no trimming or BOM stripping).

Parameters:

- `string $input`  
  CSV content.
- `string $separator`  
  Field delimiter used in the CSV content.
- `bool $header`  
  Whether the first row should be treated as the header.

Return Values:

Returns a `Generator` that yields either `array<string, string|null>` when `$header` is `true`, or `string[]` when `$header` is `false`.

Throws:

`RuntimeException` if the memory stream cannot be opened or the header row cannot be read.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`fwrite`](https://www.php.net/manual/en/function.fwrite.php)
- [`rewind`](https://www.php.net/manual/en/function.rewind.php)
- [`fgetcsv`](https://www.php.net/manual/en/function.fgetcsv.php)
- [`feof`](https://www.php.net/manual/en/function.feof.php)
- [`count`](https://www.php.net/manual/en/function.count.php)
- [`array_slice`](https://www.php.net/manual/en/function.array-slice.php)
- [`array_combine`](https://www.php.net/manual/en/function.array-combine.php)
- [`fclose`](https://www.php.net/manual/en/function.fclose.php)

### StdLib\FS\Directory::remove

Examples:

```php
use StdLib\FS\Directory;

Directory::remove('/tmp/app/cache');
```

```php
use StdLib\FS\Directory;

Directory::remove('/tmp/app', true);
```

Description:

Removes a file or directory. When `$recursive` is enabled, subdirectories are removed recursively. When `$recursive` is disabled, only files and empty directories can be removed. If the path does not exist, nothing happens.

Parameters:

- `SplFileInfo|string $filepath`  
  File or directory path to remove.
- `bool $recursive`  
  Whether to remove directories recursively.

Return Values:

Returns `void`.

Throws:

`RuntimeException` if a file or directory cannot be deleted.

See Also:

- [`is_dir`](https://www.php.net/manual/en/function.is-dir.php)
- [`is_file`](https://www.php.net/manual/en/function.is-file.php)
- [`rmdir`](https://www.php.net/manual/en/function.rmdir.php)
- [`unlink`](https://www.php.net/manual/en/function.unlink.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

## File Handling

### StdLib\FS\File::getContents

Examples:

```php
use StdLib\FS\File;

$contents = File::getContents('/etc/hosts');
```

```php
use StdLib\FS\File;

$contents = File::getContents('/missing.txt', '');
```

Description:

Reads the full contents of a file. If the file does not exist and `$default` is provided, the default value is returned instead.

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the file or an `SplFileInfo` instance.
- `string|null $default`  
  Optional default value to return when the file does not exist.

Return Values:

Returns the file contents as a string, or the default value if provided and the file does not exist.

Throws:

`RuntimeException` if the file cannot be read and no default value is provided.

See Also:

- [`file_exists`](https://www.php.net/manual/en/function.file-exists.php)
- [`file_get_contents`](https://www.php.net/manual/en/function.file-get-contents.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\File::getContentsAsInMemoryResource

Examples:

```php
use StdLib\FS\File;

$fp = File::getContentsAsInMemoryResource('/etc/hosts');
$data = stream_get_contents($fp);
```

Description:

Loads a file into a `php://memory` stream and returns the stream resource positioned at the start.

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the file or an `SplFileInfo` instance.

Return Values:

Returns a `resource` handle for the in-memory stream.

Throws:

`RuntimeException` if the memory stream cannot be opened or the file cannot be read.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`fwrite`](https://www.php.net/manual/en/function.fwrite.php)
- [`rewind`](https://www.php.net/manual/en/function.rewind.php)
- [`stream_get_contents`](https://www.php.net/manual/en/function.stream-get-contents.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\File::getLines

Examples:

```php
use StdLib\FS\File;

foreach (File::getLines('/etc/hosts') as $line) {
	echo $line . PHP_EOL;
}
```

```php
use StdLib\FS\File;

$filter = static fn(string $line) => $line === '';
foreach (File::getLines('/etc/hosts', $filter) as $line) {
	echo $line . PHP_EOL;
}
```

Description:

Reads a file line-by-line as a generator. Each line is trimmed with `rtrim($line, "\r\n")`. When `$filter` is provided, lines are yielded only when the callback returns `false` (return `true` to skip a line).

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the file or an `SplFileInfo` instance.
- `null|callable(string):bool $filter`  
  Optional filter callback. Return `true` to skip the line.

Return Values:

Returns a `Generator` that yields strings.

Throws:

`RuntimeException` if the file cannot be opened.

See Also:

- [`fopen`](https://www.php.net/manual/en/function.fopen.php)
- [`feof`](https://www.php.net/manual/en/function.feof.php)
- [`fgets`](https://www.php.net/manual/en/function.fgets.php)
- [`rtrim`](https://www.php.net/manual/en/function.rtrim.php)
- [`fclose`](https://www.php.net/manual/en/function.fclose.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)

### StdLib\FS\File::setContents

Examples:

```php
use StdLib\FS\File;

File::setContents('/tmp/example.txt', 'Hello!');
```

```php
use StdLib\FS\File;

$fp = fopen('/etc/hosts', 'rb');
File::setContents('/tmp/hosts-copy', $fp);
fclose($fp);
```

```php
use StdLib\FS\File;

File::setContents('/tmp/empty.txt', null);
```

Description:

Writes contents to a file. When `$contents` is a resource, it is read with `stream_get_contents()` and written to the file. When `$contents` is `null`, the file is created or touched.

Parameters:

- `SplFileInfo|string $filepath`  
  Path to the file or an `SplFileInfo` instance.
- `null|resource|string $contents`  
  Content to write. Accepts a resource, string, or `null`.

Return Values:

Returns `void`.

Throws:

`RuntimeException` if `$contents` is not a resource, string, or `null`.

See Also:

- [`is_resource`](https://www.php.net/manual/en/function.is-resource.php)
- [`stream_get_contents`](https://www.php.net/manual/en/function.stream-get-contents.php)
- [`is_string`](https://www.php.net/manual/en/function.is-string.php)
- [`file_put_contents`](https://www.php.net/manual/en/function.file-put-contents.php)
- [`touch`](https://www.php.net/manual/en/function.touch.php)
- [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)
