<?php

/**
 * @license GPL-2.0+
 */

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
ini_set( 'display_errors', 1 );

require __DIR__ . '/../src/JsonDumpValidator.php';
