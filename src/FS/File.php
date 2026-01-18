<?php

namespace StdLib\FS;

use RuntimeException;
use SplFileInfo;

use Generator;

class File {
	/**
	 * @param SplFileInfo|string $filepath The file to read from, either as a SplFileInfo object or a string path.
	 * @param string|null $default Optional default value to return if the file does not exist.
	 * @return string The contents of the file as a string, or the default value if provided and the file does not exist.
	 * @throws RuntimeException If the file cannot be read and no default value is provided.
	 */
	public static function getContents(SplFileInfo|string $filepath, ?string $default = null): string {
		if($filepath instanceof SplFileInfo) {
			$filepath = $filepath->getPathname();
		}

		if(!file_exists($filepath)) {
			if($default !== null) {
				return $default;
			}
			throw new RuntimeException("Could not read file: $filepath");
		}

		$contents = file_get_contents($filepath);
		if($contents === false) {
			throw new RuntimeException("Could not read file: $filepath");
		}

		return $contents;
	}

	/**
	 * @param SplFileInfo|string $filepath The file to read from, either as a SplFileInfo object or a string path.
	 * @return resource
	 */
	public static function getContentsAsInMemoryResource(SplFileInfo|string $filepath) {
		$fp = fopen('php://memory', 'wb+');
		if($fp === false) {
			throw new RuntimeException("Could not open file: $filepath");
		}
		fwrite($fp, self::getContents($filepath));
		rewind($fp);

		return $fp;
	}

	/**
	 * Get all lines from a file as a generated list of strings.
	 *
	 * @param SplFileInfo|string $filepath
	 * @param null|callable(string):bool $filter
	 * @return Generator<string>
	 */
	public static function getLines(SplFileInfo|string $filepath, $filter = null): Generator {
		if($filepath instanceof SplFileInfo) {
			$filepath = $filepath->getPathname();
		}
		$fp = fopen($filepath, 'rb');
		if($fp === false) {
			throw new RuntimeException("Could not open file: $filepath");
		}
		try {
			while(!feof($fp)) {
				$line = fgets($fp);
				if($line === false) {
					continue;
				}
				$line = rtrim($line, "\r\n");
				if($filter !== null && !$filter($line)) {
					yield $line;
				}
			}
		} finally {
			fclose($fp);
		}
	}

	/**
	 * @param SplFileInfo|string $filepath
	 * @param null|resource|string $contents If contents is a resource, it will be read and written to the file. If it is a string, it will be written to the file. If it is null, the file will be created/touched.
	 * @return void
	 */
	public static function setContents(SplFileInfo|string $filepath, $contents): void {
		if($filepath instanceof SplFileInfo) {
			$filepath = $filepath->getPathname();
		}
		if(is_resource($contents)) {
			$contents = stream_get_contents($contents);
		}
		if(is_string($contents)) {
			file_put_contents($filepath, $contents);
		} elseif($contents === null) {
			touch($filepath);
		} else {
			throw new RuntimeException('Invalid contents');
		}
	}
}