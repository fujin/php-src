--TEST--
Invalid Unicode escape sequence: Positive sign
--FILE--
<?php

var_dump("\u{+1F602}");
--EXPECTF--
Fatal error: Invalid UTF-8 codepoint escape sequence in %s on line %d
