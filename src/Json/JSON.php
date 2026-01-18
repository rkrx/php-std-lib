<?php

namespace StdLib\Json;

class JSON {
	public static function encode(mixed $contents, bool $pretty = false): string {
		return json_encode(
			$contents,
			JSON_UNESCAPED_SLASHES |
			JSON_UNESCAPED_UNICODE |
			JSON_THROW_ON_ERROR |
			($pretty ? JSON_PRETTY_PRINT : 0)
		);
	}

	public static function decode(string $contents): mixed {
		return json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
	}

	public static function decodeAssoc(string $contents): mixed {
		return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
	}
}