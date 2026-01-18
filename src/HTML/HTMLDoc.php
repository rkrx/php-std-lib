<?php

namespace StdLib\HTML;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HTMLDoc {
	private readonly DOMDocument $doc;

	/**
	 * @param string|null $content HTML content (UTF-8 by default; other encodings are auto-detected)
	 * @param string|null $charset Optional charset override (e.g. ISO-8859-1)
	 */
	public function __construct(?string $content, ?string $charset = null) {
		$this->doc = new DOMDocument('1.0', 'UTF-8');
		if(trim((string) $content) !== '') {
			$useErrors = libxml_use_internal_errors(true);
			try {
				$charsetToUse = $charset ?? 'UTF-8';
				if($charset === null && !mb_check_encoding((string) $content, 'UTF-8')) {
					$detected = mb_detect_encoding((string) $content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
					$charsetToUse = $detected ?: 'ISO-8859-1';
				}
				$content = HTMLTools::encodeEntities((string) $content, $charsetToUse);
				$this->doc->loadHTML((string) $content, LIBXML_BIGLINES | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOCDATA);
			} finally {
				libxml_use_internal_errors($useErrors);
			}
		}
		$this->doc->encoding = 'utf-8';
	}

	/**
	 * @return DOMDocument
	 */
	public function getDoc(): DOMDocument {
		return $this->doc;
	}

	/**
	 * @return DOMElement|null
	 */
	public function getRootElement(): ?DOMElement {
		return $this->doc->documentElement;
	}

	/**
	 * @param string $xpath
	 * @param DOMNode|null $parentNode
	 * @return DOMNode[]
	 */
	public function xpathFindMany(string $xpath, ?DOMNode $parentNode = null): array {
		$XPath = new DOMXPath($this->doc);
		$nodeList = $XPath->query($xpath, $parentNode);
		$result = [];
		if($nodeList === false) {
			return $result;
		}
		foreach($nodeList as $node) {
			if($node instanceof DOMNode) {
				$result[] = $node;
			}
		}
		return $result;
	}

	public function setInnerTextOfElement(DOMElement $el, ?string $text): void {
		$el->nodeValue = '';
		if($text !== null) {
			$textNode = $this->doc->createTextNode($text);
			$el->appendChild($textNode);
		}
	}
}
