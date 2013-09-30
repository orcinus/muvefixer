#!/usr/bin/php
<?
// *** PHP CONFIG ***
// ******************

$startTime = time();
ini_set("auto_detect_line_endings", true);
ini_set("max_exexution_time", 0);
ini_set("max_input_time", 0);

set_time_limit(0);


// *** INCLUDES ***
// ****************

require_once("lib/helpers.php");
require_once("lib/defaults.php");


// *** VERSION ***
// ***************

$version = "0.5";


// *** BANNER ***
// **************

echo <<<EOD

---------------------------
mUVe 3D G-Code Preprocessor
Version $version
---------------------------


EOD;


// *** PAYLOAD ***
// ***************

if(!isset($argv[1]))
	die("Usage: ./fixgcode.php <input file>\n\n");
else
	$input = $argv[1];

$inputBits = explodePath($input);
$output = $inputBits[0] . $inputBits[1] . "-mUVe." . $inputBits[2];

if(file_exists("config.txt"))
	$config = parse_ini_file("config.txt");
else
	echo "No config.txt file! Using hard-coded defaults.\n";

if(file_exists("endcode.txt"))
	$endCode = file_get_contents("endcode.txt");
else
	echo "No endcode.txt! Using hard-coded end code.\n";

// Showtime
require_once("lib/parser.php");

$endTime = time();
echo "Job finished in " . ($endTime - $startTime) . " seconds.\n\n"; die();

exit(0);
?>