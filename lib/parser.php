<?
// *** DEFINES ***
define("IS_XY", 0);		// LINE TYPE FLAGS
define("IS_Z", 	1);
define("HAS_E",	2);
define("HAS_F",	3);

define("ON",	TRUE);	// VARIOUS
define("OFF",	FALSE);
// ***************


$handleIn 	= @fopen($input, "r");
$handleOut 	= @fopen($output, 'w');

if($handleIn == FALSE)
	die("ERROR - Input file not found!\n\n");

if($handleOut == FALSE)
	die("ERROR - Couldn't create output file!\n\n");

fwrite($handleOut, "");

$delimiter = detectDelimiter($handleIn);

$layers = countLinesWithZ($handleIn, $delimiter);

if(!$layers)
	die("ERROR - No layers found!" . $delimiter . $delimiter);

echo "Found " . $layers . " layers!".$delimiter;

// Define some helper vars
$laserOff = $config["laserOffCode"] . $delimiter;
$laserOn = $config["laserOnCode"] . " S" . $config["laserPower"] . $delimiter;
if($config["syncForce"]) {
	$laserOff .= "M400" . $delimiter;
	$laserOn .= "M400" . $delimiter;
}
$laser = ON;
$layer = 0;

echo "Processing:";

// Start the actual parse
rewind($handleIn);
while(!feof($handleIn) && @$l != "Stop") {
	$l = stream_get_line($handleIn, 0, $delimiter);
	// Skip the comment lines and empty lines
	if(substr($l, 0, 1) == ";" || trim($l) == "")
		continue;
	else {
		// Figure out the line kind
		$lineTypeFlags = array(FALSE,FALSE,FALSE,FALSE);
		if(inLine($l, "G1 X"))
			$lineTypeFlags[IS_XY] 	= TRUE;
		if(inLine($l, "G1 Z"))
			$lineTypeFlags[IS_Z] 	= TRUE;
		if(inLine($l, " E"))
			$lineTypeFlags[HAS_E] 	= TRUE;
		if(inLine($l, " F"))
			$lineTypeFlags[HAS_F] 	= TRUE;

		// Process the line
		if($lineTypeFlags == array(FALSE, FALSE, FALSE, FALSE)) {	// If there's nothing to do with the
			fwrite($handleOut, $l.$delimiter);						// line, don't touch it, output as-is
		} else {
			$buffer = "";
			$l = explode(" ", $l);

			if($lineTypeFlags[IS_XY]) {				// If it's an XY move
				
				if(!$lineTypeFlags[HAS_E]) {		// If it's a jump move (no E)
					if($laser == ON)				// Turn laser off if on
						$buffer .= $laserOff;

					$buffer .= implode(" ", $l);	// Copy the jump move line

					if(!$lineTypeFlags[HAS_F]) 		// If it has no feedrate, set it
						$buffer .= " F" . number_format((float) $config["jumpSpeed"], 3, ".", "");

					$buffer .= $delimiter;			// End of line

					$buffer .= $laserOn;			// Turn the laser on
					$laser = ON;
				} else {							// If it's not a jump move (has E)
					foreach($l as $k=>$token) {		// Find the E token
						if(substr($token, 0, 1) == "E") {
							unset($l[$k]);			// Get rid of it
							break;
						}
					}
					$buffer .= implode(" ", $l);
					$buffer .= $delimiter;
				}

				fwrite($handleOut, $buffer);
			
			} elseif($lineTypeFlags[IS_Z]) {		// If it's a Z move
				++$layer;

				foreach($l as $k=>$token) {			// Find the Z coordinate
					if(substr($token, 0, 1) == "Z") {
						$layerZ = substr($token, 1);
						break;
					}
				}

				if($layer > 1) {					// First layer doesn't need a peel
					// Calculate the layer + peel height
					$zLift = number_format($layerZ + (float) $config["peelHeight"], 3, ".", "");

					$buffer .= $laserOff;			// Turn laser off
					$laser = OFF;

					$buffer .= "G1 E" . $zLift;		// Lift the right side
					$buffer .= " F" . number_format((float) $config["peelLiftSpeed"], 3, ".", "");
					$buffer .= $delimiter;

					$buffer .= "G4 P250" . $delimiter;	// Wait a bit

					$buffer .= "G1 Z" . $zLift;		// Lift the left side
					$buffer .= " F" . number_format((float) $config["peelLiftSpeed"], 3, ".", "");
					$buffer .= $delimiter;

					if($layer == 2)					// First peel has a longer pause
						$buffer .= "G4 P" . $config["basePause"] . $delimiter;
					else 							// Consecutive peels have a short pause
						$buffer .= "G4 P250" . $delimiter;
				}

				$buffer .= "G1 Z" . $layerZ;		// Lower the platform back 
				$buffer .= " E" . $layerZ;
				$buffer .= " F" . number_format((float) $config["peelDropSpeed"], 3, ".", "");
				$buffer .= $delimiter;

				if($layer > 1) {					// First layer skips the extra laser logic
					$buffer .= "G4 P" . $config["peelPause"] . $delimiter;
				}

				fwrite($handleOut, $buffer);

				echo progressBar($layer, $layers, "30");
			} else {								// If it's anything else, copy it
				if(!$lineTypeFlags[HAS_E])			// Unless it's messing with the E axis
					fwrite($handleOut, implode(" ",$l).$delimiter);
			}
		}
	}
}

// Add the ending G code block
echo "\nDone.\n";
echo "Adding the G-code post-script code.\n";
fwrite($handleOut, $endCode . $delimiter);

// Close the file handles
fclose($handleIn);
fclose($handleOut);

