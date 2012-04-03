--TEST--
Bug #61367: open_basedir bypass in libxml RSHUTDOWN: read test
--SKIPIF--
<?php if(!extension_loaded('dom')) echo 'skip'; ?>
--INI--
open_basedir=.
; Suppress spurious "Trying to get property of non-object" notices
error_reporting=E_ALL & ~E_NOTICE
--FILE--
<?php

class StreamExploiter {
	public function stream_close (  ) {
		$doc = new DOMDocument;
		$doc->resolveExternals = true;
		$doc->substituteEntities = true;
		$dir = htmlspecialchars(dirname(getcwd()));
		$doc->loadXML( <<<XML
<!DOCTYPE doc [
	<!ENTITY file SYSTEM "file:///$dir/bad">
]>
<doc>&file;</doc>
XML
		);
		print $doc->documentElement->firstChild->nodeValue;
	}

	public function stream_open (  $path ,  $mode ,  $options ,  &$opened_path ) {
		return true;
	}
}

var_dump(mkdir('test_bug_61367'));
var_dump(mkdir('test_bug_61367/base'));
var_dump(file_put_contents('test_bug_61367/bad', 'blah'));
var_dump(chdir('test_bug_61367/base'));

stream_wrapper_register( 'exploit', 'StreamExploiter' );
$s = fopen( 'exploit://', 'r' );

?>
--CLEAN--
<?php
unlink('test_bug_61367/bad');
rmdir('test_bug_61367/base');
rmdir('test_bug_61367');
?>
--EXPECTF--
bool(true)
bool(true)
int(4)
bool(true)

Warning: DOMDocument::loadXML(): I/O warning : failed to load external entity "file:///%s/test_bug_61367/bad" in %s on line %d

Warning: DOMDocument::loadXML(): Failure to process entity file in Entity, line: 4 in %s on line %d

Warning: DOMDocument::loadXML(): Entity 'file' not defined in Entity, line: 4 in %s on line %d