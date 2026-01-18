<?php

declare(strict_types=1);

namespace StdLib\HTML;

use PHPUnit\Framework\TestCase;

final class HTMLToolsTest extends TestCase {
	public static function setUpBeforeClass(): void {
		require_once __DIR__ . '/../../src/HTML/HTMLTools.php';
	}

	public function testEncodeEntitiesConvertsNonUtf8Input(): void {
		$input = iconv('UTF-8', 'ISO-8859-1', "M\u{00FC}nchen");
		if($input === false) {
			$this->fail('iconv failed to create ISO-8859-1 input.');
		}

		$encoded = HTMLTools::encodeEntities($input, 'ISO-8859-1');

		$this->assertSame('M&#252;nchen', $encoded);
	}
}
