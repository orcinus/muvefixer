#!/usr/bin/php
<?

$startTime = time();
ini_set("auto_detect_line_endings", true);
ini_set("max_exexution_time", 0);
ini_set("max_input_time", 0);

set_time_limit(0);

require_once("lib/helpers.php");
require_once("conf/config.php");
require_once("conf/endcode.php");

echo <<<EOD
---------------------------
mUVe 3D G-Code Preprocessor
Version $version
---------------------------


EOD;

if(!isset($argv[1]))
	die("Usage: ./fixgcode.php <input file>\n\n");
else
	$input = $argv[1];

$inputBits = explodePath($input);
$output = $inputBits[0] . $inputBits[1] . "-mUVe." . $inputBits[2];

// Showtime
require_once("lib/parser.php");

$endTime = time();
echo "Job finished in " . ($endTime - $startTime) . " seconds.\n\n"; die();

?>