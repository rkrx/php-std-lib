# JSON

## JSON Handling

### StdLib\Json\JSON::encode

Examples:

```php
use StdLib\Json\JSON;

$payload = ['a' => 1, 'b' => 'text'];
$json = JSON::encode($payload);
```

```php
use StdLib\Json\JSON;

$json = JSON::encode(['a' => 1], true);
```

Description:

Encodes a PHP value into a JSON string. Uses `JSON_THROW_ON_ERROR`, `JSON_UNESCAPED_SLASHES`, and `JSON_UNESCAPED_UNICODE`, with optional pretty printing.

Parameters:

- `mixed $contents`  
  Value to encode.
- `bool $pretty`  
  Whether to pretty-print the JSON output.

Return Values:

Returns the encoded JSON string.

Throws:

`JsonException` when encoding fails.

See Also:

- [`json_encode`](https://www.php.net/manual/en/function.json-encode.php)
- [`JsonException`](https://www.php.net/manual/en/class.jsonexception.php)

### StdLib\Json\JSON::decode

Examples:

```php
use StdLib\Json\JSON;

$payload = JSON::decode('{"a":1}');
echo $payload->a;
```

Description:

Decodes JSON into a PHP object (stdClass) with strict error handling.

Parameters:

- `string $contents`  
  JSON string to decode.

Return Values:

Returns the decoded value, typically an object.

Throws:

`JsonException` when decoding fails.

See Also:

- [`json_decode`](https://www.php.net/manual/en/function.json-decode.php)
- [`JsonException`](https://www.php.net/manual/en/class.jsonexception.php)

### StdLib\Json\JSON::decodeAssoc

Examples:

```php
use StdLib\Json\JSON;

$data = JSON::decodeAssoc('{"a":1}');
echo $data['a'];
```

Description:

Decodes JSON into associative arrays with strict error handling.

Parameters:

- `string $contents`  
  JSON string to decode.

Return Values:

Returns the decoded value, typically an array.

Throws:

`JsonException` when decoding fails.

See Also:

- [`json_decode`](https://www.php.net/manual/en/function.json-decode.php)
- [`JsonException`](https://www.php.net/manual/en/class.jsonexception.php)
