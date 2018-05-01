<?php

namespace Wikibase\DumpValidator;

/**
 * Checks whether the structure and content of a given JSON dump is plausible.
 *
 * @license GPL-2.0-or-later
 * @author Marius Hoch
 */
class JsonDumpValidator {

	/**
	 * @var int[] Entity id -> line number map showing already encountered entities
	 */
	private $encountered = [];

	/**
	 * @param resource $file
	 *
	 * @return bool|string True if the dump is valid, string error otherwise
	 */
	public function validate( $file ) {
		$this->encountered = [];

		$opening = fgets( $file );
		$lineNumber = 1;
		if ( !$opening ) {
			return 'Missing JSON opener on line ' . $lineNumber;
		}
		if ( $opening !== "[\n" ) {
			return 'Invalid JSON opener on line ' . $lineNumber . ': Expected "[\n", but got ' . json_encode( $opening );
		}

		$endOfData = false;
		while ( !$endOfData ) {
			$str = fgets( $file );
			if ( $str === false ) {
				return 'Unexpected end of dump after line ' . $lineNumber;
			}
			$lineNumber++;
			if ( substr( $str, -1, 1 ) !== "\n" ) {
				return 'Unexpected end of dump on line ' . $lineNumber;
			}
			if ( substr( $str, -2, 1 ) === ',' ) {
				$str = substr( $str, 0, -2 );
			} else {
				$str = substr( $str, 0, -1 );
				// No trailing comma, this has to be the last entity
				$endOfData = true;
			}

			$entity = json_decode( $str, true );
			if ( !is_array( $entity ) ) {
				return "Couldn't decode entity on line $lineNumber: " . json_last_error_msg();
			}
			$result = $this->assertValidEntity( $entity, $lineNumber );
			if ( $result !== true ) {
				return $result;
			}
		}

		$closing = fgets( $file );
		if ( !$closing ) {
			return 'Missing JSON closing after line ' . $lineNumber;
		}
		$lineNumber++;
		if ( $closing !== "]\n" ) {
			return 'Invalid JSON closing on line ' . $lineNumber . ': Expected "]\n", but got ' . json_encode( $closing );
		}

		return true;
	}

	/**
	 * @param array $entity
	 * @param int $lineNumber
	 *
	 * @return bool|string True if the entity is valid, string error otherwise
	 */
	public function assertValidEntity( array $entity, $lineNumber ) {
		if ( !isset( $entity['id'] ) ) {
			return "Missing entity id on line $lineNumber";
		}
		$id = $entity['id'];
		if ( isset( $this->encountered[$id] ) ) {
			return "Duplicate entity $id on line $lineNumber, already encountered on line " . $this->encountered[$id];
		}
		$this->encountered[$id] = $lineNumber;

		return true;
	}

}
