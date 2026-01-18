<?php

declare(strict_types=1);

namespace StdLib\Json;

use PHPUnit\Framework\TestCase;
use StdLib\Json\JSON;
use JsonException;

final class JSONTest extends TestCase {
	public function testEncodeDecodeRoundTrip(): void {
		$data = [
			'a' => 1,
			'b' => 'text',
			'c' => ['nested' => true],
		];

		$json = JSON::encode($data);
		/** @var object{a:int, b:string, c:object{nested:bool}} $decoded */
		$decoded = JSON::decode($json);

		$this->assertSame(1, $decoded->a);
		$this->assertSame('text', $decoded->b);
		$this->assertTrue($decoded->c->nested);
	}

	public function testDecodeAssoc(): void {
		$data = ['a' => 1, 'b' => 'text'];
		$json = JSON::encode($data);

		$decoded = JSON::decodeAssoc($json);

		$this->assertSame($data, $decoded);
	}

	public function testEncodePretty(): void {
		$json = JSON::encode(['a' => 1], true);

		$this->assertStringContainsString("\n", $json);
		$this->assertStringContainsString('  "a": 1', $json);
	}

	public function testDecodeInvalidThrows(): void {
		$this->expectException(JsonException::class);

		JSON::decode('{');
	}
}
