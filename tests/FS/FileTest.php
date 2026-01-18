<?php

declare(strict_types=1);

namespace StdLib\FS;

use PHPUnit\Framework\TestCase;
use StdLib\FS\File;
use RuntimeException;

final class FileTest extends TestCase {
	/** @var list<string> */
	private array $tempPaths = [];

	protected function tearDown(): void {
		foreach($this->tempPaths as $path) {
			if(is_file($path)) {
				@unlink($path);
			}
		}
		$this->tempPaths = [];
	}

	public function testGetContentsAndDefault(): void {
		$file = $this->makeTempFile('file-contents', 'hello');

		$this->assertSame('hello', File::getContents($file));
		$this->assertSame('default', File::getContents($file . '-missing', 'default'));

		$this->expectException(RuntimeException::class);
		File::getContents($file . '-missing-2');
	}

	public function testGetContentsAsInMemoryResource(): void {
		$file = $this->makeTempFile('file-resource', 'resource-content');

		$resource = File::getContentsAsInMemoryResource($file);

		$this->assertTrue(is_resource($resource));
		$this->assertSame('resource-content', stream_get_contents($resource));
		fclose($resource);
	}

	public function testGetLinesWithFilter(): void {
		$file = $this->makeTempFile('file-lines', "keep\nskip\nkeep2\n");

		$lines = iterator_to_array(File::getLines($file, static fn(string $line): bool => $line === 'skip'));

		$this->assertSame(['keep', 'keep2'], $lines);
	}

	public function testSetContentsStringResourceAndNull(): void {
		$file = $this->makeTempFile('file-set-string', '');
		File::setContents($file, 'abc');
		$this->assertSame('abc', file_get_contents($file));

		$resource = fopen('php://memory', 'wb+');
		if($resource === false) {
			$this->fail('Could not open memory stream');
		}
		fwrite($resource, 'xyz');
		rewind($resource);
		File::setContents($file, $resource);
		fclose($resource);
		$this->assertSame('xyz', file_get_contents($file));

		$touchFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'file-touch-' . bin2hex(random_bytes(4));
		$this->tempPaths[] = $touchFile;
		File::setContents($touchFile, null);
		$this->assertTrue(file_exists($touchFile));
	}

	private function makeTempFile(string $prefix, string $contents): string {
		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '-' . bin2hex(random_bytes(4));
		file_put_contents($path, $contents);
		$this->tempPaths[] = $path;
		return $path;
	}
}
