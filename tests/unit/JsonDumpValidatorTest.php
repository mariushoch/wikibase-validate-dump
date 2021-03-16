<?php

namespace Wikibase\DumpValidator\Tests;

use PHPUnit\Framework\TestCase;
use Wikibase\DumpValidator\JsonDumpValidator;

/**
 * @covers JsonDumpValidator
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch
 */
class JsonDumpValidatorTest extends TestCase {

	public function provideJson() {
		return [
			'empty dump' => [
				"Couldn't decode entity on line 2: Syntax error",
				"[\n]\n",
			],
			'missing opening' => [
				'Missing JSON opener on line 1',
				'',
			],
			'invalid opening' => [
				'Invalid JSON opener on line 1: Expected "[\n", but got "a[\n"',
				"a[\n]\n",
			],
			'unexpected end 0' => [
				'Unexpected end of dump after line 1',
				"[\n",
			],
			'unexpected end 1' => [
				'Unexpected end of dump on line 2',
				"[\n" . '{"id":"Q42"},',
			],
			'unexpected end 2' => [
				'Unexpected end of dump after line 2',
				"[\n" . '{"id":"Q42"},' . "\n",
			],
			'JSON syntax error' => [
				"Couldn't decode entity on line 2: Syntax error",
				"[\n" . '{"id"::"Q12"}' . "\n]\n",
			],
			'Missing entity id' => [
				'Missing entity id on line 2',
				"[\n" . '{}' . "\n]\n",
			],
			'Duplicate entity id' => [
				'Duplicate entity Q42 on line 3, already encountered on line 2',
				"[\n" . '{"id":"Q42"},' . "\n" . '{"id":"Q42"}' . "\n]\n",
			],
			'Duplicate lexeme id' => [
				"Duplicate entity L4 on line 3, already encountered on line 2",
				"[\n" . '{"type":"lexeme","id":"L4","lemmas":"blah"},' . "\n" . '{"type":"lexeme","id":"L4"}' . "\n]\n",
			],
			'Duplicate entity id, detection disabled' => [
				true,
				"[\n" . '{"id":"Q42"},' . "\n" . '{"id":"Q42"}' . "\n]\n",
				false
			],
			'missing trailing comma' => [
				'Invalid JSON closing on line 3: Expected "]\n", but got "{\"id\":\"Q33\"}"',
				"[\n" . '{"id":"Q42"}' . "\n" . '{"id":"Q33"}',
			],
			'missing closing' => [
				'Missing JSON closing after line 2',
				"[\n" . '{"id":"Q42"}' . "\n",
			],
			'invalid closing' => [
				'Invalid JSON closing on line 3: Expected "]\n", but got "]trailing\n"',
				"[\n" . '{"id":"Q42"}' . "\n]trailing\n",
			],
			'valid simple dump' => [
				true,
				"[\n" . '{"id":"Q12"}' . "\n]\n",
			],
			'valid dump' => [
				true,
				fopen( 'compress.zlib://' . __DIR__ . '/../wikidata-20171028-all-first2500.json.gz', 'r' ),
			],
			'invalid lexeme dump' => [
				"Couldn't decode entity on line 2: Syntax error",
				fopen( __DIR__ . '/../wikidata-20210310-lexemes-head-n10.json', 'r' ),
			],
			'valid lexeme dump' => [
				true,
				fopen( 'compress.zlib://' . __DIR__ . '/../wikidata-20210310-lexemes-first2500.json.gz', 'r' ),
			],
		];
	}

	/**
	 * @dataProvider provideJson
	 */
	public function testValidate( $expected, $json, $checkForDuplicates = true ) {
		if ( is_string( $json ) ) {
			$file = tmpfile();
			fwrite( $file, $json );
		} else {
			$file = $json;
		}
		rewind( $file );

		$jsonDumpValidator = new JsonDumpValidator( $checkForDuplicates );
		$result = $jsonDumpValidator->validate( $file );

		$this->assertSame( $expected, $result );
	}

}
