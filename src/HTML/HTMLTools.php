<?php

namespace StdLib\HTML;

use DOMElement;
use Exception;
use RuntimeException;
use ValueError;

class HTMLTools {
	/**
	 * @template T of (null|string)
	 * @param T $htmlContent
	 * @param string $charset
	 * @return T
	 * @throws Exception
	 */
	public static function encodeEntities(?string $htmlContent, string $charset = 'UTF-8'): ?string {
		if($htmlContent === null) {
			return null;
		}
		set_error_handler(static fn() => throw new Exception());
		try {
			$isUtf8 = strcasecmp($charset, 'UTF-8') === 0;
			if(!$isUtf8) {
				$htmlContent = iconv($charset, 'UTF-8', $htmlContent);
				if($htmlContent === false) {
					$htmlContent = null;
					throw new RuntimeException('Could not convert charset to UTF-8.');
				}
			} elseif(!mb_check_encoding($htmlContent, 'UTF-8')) {
				$htmlContent = iconv('UTF-8', 'UTF-8', $htmlContent);
				if($htmlContent === false) {
					$htmlContent = null;
					throw new RuntimeException('Could not convert charset to UTF-8.');
				}
			}
			return mb_encode_numericentity($htmlContent, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
		} catch (Exception|ValueError) {
			try {
				$htmlContent = mb_encode_numericentity($htmlContent, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
			} catch (Exception|ValueError) {
			}
			/** @phpstan-ignore-next-line */
			return $htmlContent;
		} finally {
			restore_error_handler();
		}
	}

	/**
	 * Will convert HTML to text
	 *
	 * @param string|null $html The HTML-Contents to convert to text
	 * @param array{excludeTags?: string[]} $options
	 * @return string|null
	 */
	public static function htmlToText(?string $html, array $options = []): ?string {
		if($html === null) {
			return null;
		}

		$options['excludeTags'] = $options['excludeTags'] ?? [
			'canvas', 'svg', 'script', 'style', 'object',
			'embed', 'audio', 'video', 'frame', 'iframe',
			'head', 'textarea', 'select', 'map', 'progress',
			'menu', 'nav', 'search', 'tfoot', 'template'
		];

		if(trim((string) $html)) {
			$doc = new HTMLDoc($html);
			foreach($doc->xpathFindMany('//ol') as $ol) {
				foreach($doc->xpathFindMany('./li', $ol) as $i => $li) {
					/** @var DOMElement $li */
					$newValue = ($i+1) . '. ' . preg_replace("{\\s+}", ' ', $li->textContent) . "\n";
					$doc->setInnerTextOfElement($li, $newValue);
				}
			}

			foreach($doc->xpathFindMany('//ul') as $list) {
				foreach($doc->xpathFindMany('./li', $list) as $li) {
					/** @var DOMElement $li */
					$newValue = '- ' . preg_replace("{\\s+}", ' ', $li->textContent) . "\n";
					$doc->setInnerTextOfElement($li, $newValue);
				}
			}

			foreach($options['excludeTags'] as $tagToExclude) {
				foreach($doc->xpathFindMany("//$tagToExclude") as $el) {
					$el->parentNode?->removeChild($el);
				}
			}

			$compactWhitespace = static fn(string $text) => preg_replace('{\s+}', ' ', trim($text));

			foreach($doc->xpathFindMany('//table') as $table) {
				$tableContentLines = [];

				$headerColumns = $doc->xpathFindMany('./thead[1]/tr[1]/th | ./tr[1]/th', $table);
				$headerColumnContents = [];

				foreach($headerColumns as $idx => $headerColumn) {
					$headerColumnContents[$idx] = $compactWhitespace($headerColumn->textContent ?? '');
				}

				$rows = $doc->xpathFindMany('./tbody/tr | ./tr', $table);
				foreach($rows as $row) {
					$rowColumnContents = [];
					$columns = $doc->xpathFindMany('./td', $row);
					foreach($columns as $idx => $rowColumn) {
						$headerColumnContent = $headerColumnContents[$idx] ?? '';
						$rowColumnContent = $compactWhitespace($rowColumn->textContent ?? '');
						if($headerColumnContent) {
							$rowColumnContents[] = sprintf('%s: %s', $headerColumnContent, $rowColumnContent);
						} else {
							$rowColumnContents[] = $rowColumnContent;
						}
					}
					$tableContentLines[] = implode("\n", $rowColumnContents);
				}

				$tableContents = implode("\n\n", $tableContentLines);

				$table->textContent = $tableContents;
			}

			$html = (string) ($doc->getRootElement() ?? ((object) ['textContent' => null]))->textContent;
		}
		$lines = explode("\n", (string) $html);
		$lines = array_map('trim', $lines);
		$text = implode("\n", $lines);
		$text = (string) preg_replace('{(?:\r?\n){3,}}', "\n\n", $text);
		return trim($text);
	}
}
