<?php

namespace StdLib\FS;

use RuntimeException;
use SplFileInfo;
use Generator;

class CSVFile {
	/**
	 * @param SplFileInfo|string $filepath
	 * @param string $separator
	 * @param bool $header
	 * @return Generator<array<string, string>>
	 */
	public static function getCSVLinesFromFile(SplFileInfo|string $filepath, string $separator = ',', string $enclosure = '"', bool $header = true): Generator {
		if($filepath instanceof SplFileInfo) {
			$filepath = $filepath->getPathname();
		}
		$fp = fopen($filepath, 'rb');
		if($fp === false) {
			throw new RuntimeException("Could not open file: $filepath");
		}
		try {
			$headerRow = null;
			$headerRowColumnCount = null;
			if($header) {
				$headerRow = fgetcsv($fp, 0, $separator, $enclosure);
				if($headerRow === false) {
					throw new RuntimeException("Could not read header row from file: $filepath");
				}
				$headerRow = array_map('trim', $headerRow);
				$headerRow = array_map(static fn(string $col) => strtr($col, ["\xEF\xBB\xBF" => '']), $headerRow);
				$headerRowColumnCount = count($headerRow);
			}
			while(!feof($fp)) {
				$line = fgetcsv($fp, 0, $separator);
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

	/**
	 * @param iterable<string[]> $lines
	 * @param string $filepath
	 * @param string $separator
	 * @param bool $useHeader
	 * @return void
	 */
	public static function writeCSVFile(iterable $lines, string $filepath, string $separator, bool $useHeader = true): void {
		$fp = fopen($filepath, 'wb');
		if($fp === false) {
			throw new RuntimeException("Could not open file: $filepath");
		}
		try {
			if($useHeader) {
				$header = null;
				foreach($lines as $line) {
					if($header === null) {
						$header = array_keys($line);
						fputcsv($fp, $header, $separator);
					}
					fputcsv($fp, $line, $separator);
				}
			} else {
				foreach($lines as $line) {
					fputcsv($fp, $line, $separator);
				}
			}
		} finally {
			fclose($fp);
		}
	}
}