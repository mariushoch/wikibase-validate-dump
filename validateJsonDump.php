<?php

require_once __DIR__ . '/src/JsonDumpValidator.php';

if ( $argc < 2 || $argv[1] === '--help' ) {
	echo 'Usage: validateJsonDump.php [--checkForDuplicates] theDumpFile' . PHP_EOL;
	echo "\t--checkForDuplicates: Whether to check for duplicate entities in the dump (needs 4GiB+ memory for Wikidata dumps)" . PHP_EOL;
	echo "\t" . 'Prepend "compress.zlib://" to the dump file name to read gzip files, "compress.bzip2://" for bzip2 files.' . PHP_EOL . PHP_EOL;

	exit;
}

$checkForDuplicates = false;
if ( $argv[1] === '--checkForDuplicates' ) {
	$checkForDuplicates = true;
	$fileName = $argv[2];
} else {
	$fileName = $argv[1];
}

$f = @fopen( $fileName, 'r' );
if ( !is_resource( $f ) ) {
	echo "File \"$fileName\" can't be read." . PHP_EOL;
	exit( 1 );
}

$jsonDumpValidator = new Wikibase\DumpValidator\JsonDumpValidator( $checkForDuplicates );
$result = $jsonDumpValidator->validate( $f );

if ( $result === true ) {
	echo 'Success!' . PHP_EOL;
} else {
	echo "Dump is probably invalid:" . PHP_EOL . PHP_EOL;
	echo $result . PHP_EOL;
	exit( 1 );
}
