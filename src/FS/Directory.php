<?php

namespace StdLib\FS;

use CallbackFilterIterator;
use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Iterator;

class Directory {
	/**
	 * @param string $directory
	 * @param bool $recursive
	 * @param null|callable(SplFileInfo):bool $filter
	 * @return Iterator<SplFileInfo>
	 */
	public static function allItemsInDirectory(string $directory, bool $recursive = false, $filter = null): Iterator {
		/** @var Iterator<SplFileInfo> $iterator */
		$iterator = match($recursive) {
			true => new RecursiveIteratorIterator(
			    new RecursiveDirectoryIterator(
			        $directory,
			        FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
			    ),
			    RecursiveIteratorIterator::SELF_FIRST
			),
			false => new FilesystemIterator(
				$directory,
				FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
			)
		};

		if($filter !== null) {
			return new CallbackFilterIterator($iterator, $filter);
		}

		return $iterator;
	}

	/**
	 * @param SplFileInfo|string $filepath
	 * @return Generator<string>
	 */
	public static function readLinesFromFile(SplFileInfo|string $filepath): Generator {
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
				yield rtrim($line);
			}
		} finally {
			fclose($fp);
		}
	}

	public static function mkdir(string $string): bool {
		if(file_exists($string)) {
			return true;
		}

		if(!mkdir($string, 0777, true) && !is_dir($string)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $string));
		}

		return true;
	}

	/**
	 * @param string $input
	 * @param string $separator
	 * @param bool $header
	 * @param string $enclosure
	 * @param string $escape
	 * @return Generator<array<string, string>>
	 */
	public static function getCSVLinesFromString(string $input, string $separator, bool $header = true, string $enclosure = '"', string $escape = '\\') {
		$fp = fopen('php://memory', 'wb+');
		if($fp === false) {
			throw new RuntimeException('Could not open memory stream');
		}
		fwrite($fp, $input);
		rewind($fp);
		$headerRow = null;
		$headerRowColumnCount = null;
		try {
			if($header) {
				$headerRow = fgetcsv($fp, 0, $separator, $enclosure, $escape);
				if($headerRow === false) {
					throw new RuntimeException('Could not read header row from string');
				}
				$headerRowColumnCount = count($headerRow);
			}
			while(!feof($fp)) {
				$line = fgetcsv($fp, 0, $separator, $enclosure, $escape);
				if(!is_array($line)) {
					break;
				}
				if($headerRow !== null) {
					while(count($line) < $headerRowColumnCount) {
						$line[] = null;
					}
					$line = array_slice($line, 0, $headerRowColumnCount);
					yield array_combine($headerRow, $line);
				} else {
					yield $line;
				}
			}
		} finally {
			fclose($fp);
		}
	}

	public static function remove(SplFileInfo|string $filepath, bool $recursive = false): void {
		if($filepath instanceof SplFileInfo) {
			$filepath = $filepath->getPathname();
		}
		if(is_dir($filepath)) {
			$items = self::allItemsInDirectory(
				directory: $filepath,
				recursive: $recursive
			);
			foreach($items as $item) {
				if($recursive && $item->isDir()) {
					self::remove(
						filepath: $item->getPathname(),
						recursive: $recursive
					);
				} else {
					self::removeSingleItem($item->getPathname());
				}
			}
			rmdir($filepath);
		} elseif(is_file($filepath)) {
			self::removeSingleItem($filepath);
		}
	}

	private static function removeSingleItem(string $path): void {
		if(is_dir($path)) {
			if(!rmdir($path)) {
				throw new RuntimeException("Cannot delete directory $path");
			}
		} elseif(is_file($path)) {
			if(!unlink($path)) {
				throw new RuntimeException("Cannot delete file $path");
			}
		}
	}
}
