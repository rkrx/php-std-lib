<?php

declare(strict_types=1);

namespace StdLib\FS;

use PHPUnit\Framework\TestCase;
use StdLib\FS\CSVFile;

final class CSVFileTest extends TestCase {
	private array $tempPaths = [];

	protected function tearDown(): void {
		foreach($this->tempPaths as $path) {
			if(is_file($path)) {
				@unlink($path);
			}
		}
		$this->tempPaths = [];
	}

	public function testWriteAndReadWithHeader(): void {
		$file = $this->makeTempFile('csv-write-read');
		$lines = [
			['a' => '1', 'b' => '2'],
			['a' => '3', 'b' => '4'],
		];

		CSVFile::writeCSVFile($lines, $file, ',', true);

		$read = iterator_to_array(CSVFile::getCSVLinesFromFile($file, ',', '"', true));

		$this->assertSame($lines, $read);
	}

	public function testReadWithoutHeader(): void {
		$file = $this->makeTempFile('csv-no-header');
		file_put_contents($file, "1,2\n3,4\n");

		$read = iterator_to_array(CSVFile::getCSVLinesFromFile($file, ',', '"', false));

		$this->assertSame([
			['1', '2'],
			['3', '4'],
		], $read);
	}

	public function testHeaderBOMStrippedAndTrimmed(): void {
		$file = $this->makeTempFile('csv-bom');
		file_put_contents($file, " \xEF\xBB\xBFcol1 , col2 \n1,2\n");

		$read = iterator_to_array(CSVFile::getCSVLinesFromFile($file, ',', '"', true));

		$this->assertSame([
			['col1' => '1', 'col2' => '2'],
		], $read);
	}

	private function makeTempFile(string $prefix): string {
		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '-' . bin2hex(random_bytes(4)) . '.csv';
		file_put_contents($path, '');
		$this->tempPaths[] = $path;
		return $path;
	}
}
