# Wikibase Validate Dump
Helper for testing whether a given Wikibase entity dump's structure is valid.

This is run as cron job on `stat1005.eqiad.wmnet` ([details](https://gist.github.com/mariushoch/b44bf04146d507e3f8e2881872e01e9a)).

## Example usage:
* `php validateJsonDump.php compress.zlib://tests/wikidata-20171028-all-first2500.json.gz`