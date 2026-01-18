<?php

declare(strict_types=1);

namespace StdLib\HTML;

use PHPUnit\Framework\TestCase;

final class HTMLDocTest extends TestCase {
	public static function setUpBeforeClass(): void {
		require_once __DIR__ . '/../../src/HTML/HTMLTools.php';
		require_once __DIR__ . '/../../src/HTML/HTMLDoc.php';
	}

	public function testHtmlDocLoadsNonUtf8Content(): void {
		$htmlUtf8 = "<p>M\u{00FC}nchen</p>";
		$htmlIso = iconv('UTF-8', 'ISO-8859-1', $htmlUtf8);
		if($htmlIso === false) {
			$this->fail('iconv failed to create ISO-8859-1 HTML input.');
		}

		$doc = new HTMLDoc($htmlIso);
		$root = $doc->getRootElement();

		$this->assertNotNull($root);
		$this->assertSame("M\u{00FC}nchen", $root->textContent);
	}
}
