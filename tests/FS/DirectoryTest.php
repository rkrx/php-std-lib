<?php

declare(strict_types=1);

namespace StdLib\FS;

use PHPUnit\Framework\TestCase;
use StdLib\FS\Directory;
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

final class DirectoryTest extends TestCase {
	private array $tempPaths = [];

	protected function tearDown(): void {
		foreach($this->tempPaths as $path) {
			$this->removePath($path);
		}
		$this->tempPaths = [];
	}

	public function testAllItemsInDirectoryNonRecursive(): void {
		$dir = $this->makeTempDir('dir-non-recursive');
		$file = $dir . DIRECTORY_SEPARATOR . 'file.txt';
		$subdir = $dir . DIRECTORY_SEPARATOR . 'subdir';
		file_put_contents($file, 'hello');
		mkdir($subdir);

		$items = Directory::allItemsInDirectory($dir, false);
		$names = [];
		foreach($items as $item) {
			$names[] = $item->getFilename();
		}
		sort($names);

		$this->assertSame(['file.txt', 'subdir'], $names);
	}

	public function testAllItemsInDirectoryRecursiveWithFilter(): void {
		$dir = $this->makeTempDir('dir-recursive');
		file_put_contents($dir . DIRECTORY_SEPARATOR . 'file.txt', 'hello');
		file_put_contents($dir . DIRECTORY_SEPARATOR . 'file.log', 'log');
		$subdir = $dir . DIRECTORY_SEPARATOR . 'sub';
		mkdir($subdir);
		file_put_contents($subdir . DIRECTORY_SEPARATOR . 'inner.txt', 'inner');

		$items = Directory::allItemsInDirectory(
			directory: $dir,
			recursive: true,
			filter: static function(SplFileInfo $info): bool {
				return $info->isFile() && $info->getExtension() === 'txt';
			}
		);

		$paths = [];
		foreach($items as $item) {
			$paths[] = str_replace($dir . DIRECTORY_SEPARATOR, '', $item->getPathname());
		}
		sort($paths);

		$this->assertSame(['file.txt', 'sub' . DIRECTORY_SEPARATOR . 'inner.txt'], $paths);
	}

	public function testReadLinesFromFile(): void {
		$dir = $this->makeTempDir('dir-read-lines');
		$file = $dir . DIRECTORY_SEPARATOR . 'lines.txt';
		file_put_contents($file, "one\nTwo   \n");

		$lines = iterator_to_array(Directory::readLinesFromFile($file));

		$this->assertSame(['one', 'Two'], $lines);
	}

	public function testMkdirCreatesNestedDirectory(): void {
		$dir = $this->makeTempDir('dir-mkdir');
		$nested = $dir . DIRECTORY_SEPARATOR . 'a' . DIRECTORY_SEPARATOR . 'b';

		$this->assertTrue(Directory::mkdir($nested));
		$this->assertTrue(is_dir($nested));
		$this->assertTrue(Directory::mkdir($nested));
	}

	public function testGetCSVLinesFromStringWithHeader(): void {
		$input = "a,b\n1,2\n3,4\n";

		$lines = iterator_to_array(Directory::getCSVLinesFromString($input, ',', true));

		$this->assertSame([
			['a' => '1', 'b' => '2'],
			['a' => '3', 'b' => '4'],
		], $lines);
	}

	public function testGetCSVLinesFromStringWithoutHeader(): void {
		$input = "1,2\n3,4\n";

		$lines = iterator_to_array(Directory::getCSVLinesFromString($input, ',', false));

		$this->assertSame([
			['1', '2'],
			['3', '4'],
		], $lines);
	}

	public function testRemoveRemovesFileAndDirectoryTree(): void {
		$dir = $this->makeTempDir('dir-remove');
		file_put_contents($dir . DIRECTORY_SEPARATOR . 'first.txt', 'content');
		file_put_contents($dir . DIRECTORY_SEPARATOR . 'second.txt', 'content');

		Directory::remove($dir, true);
		$this->assertFalse(file_exists($dir));

		$file = $this->makeTempFile('single-file');
		Directory::remove($file);
		$this->assertFalse(file_exists($file));
	}

	private function makeTempDir(string $prefix): string {
		$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '-' . bin2hex(random_bytes(4));
		mkdir($dir, 0777, true);
		$this->tempPaths[] = $dir;
		return $dir;
	}

	private function makeTempFile(string $prefix): string {
		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '-' . bin2hex(random_bytes(4));
		file_put_contents($path, 'temp');
		$this->tempPaths[] = $path;
		return $path;
	}

	private function removePath(string $path): void {
		if(is_file($path)) {
			@unlink($path);
			return;
		}
		if(!is_dir($path)) {
			return;
		}
		$items = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach($items as $item) {
			if($item->isDir()) {
				@rmdir($item->getPathname());
			} else {
				@unlink($item->getPathname());
			}
		}
		@rmdir($path);
	}
}
