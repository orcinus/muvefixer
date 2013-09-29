<?
// *** DEFINES ***
define("IS_XY", 0);		// LINE TYPE FLAGS
define("IS_Z", 	1);
define("HAS_E",	2);
define("HAS_F",	3);

define("ON",	TRUE);	// VARIOUS
define("OFF",	FALSE);
// ***************


$handle = @fopen($input, "r");

if($handle == FALSE)
	die("ERROR - Input file not found!\n\n");

file_put_contents($output, "");

$delimiter = detectDelimiter($handle);

$layers = countLinesWithZ($handle, $delimiter);

if(!$layers)
	die("ERROR - No layers found!" . $delimiter . $delimiter);

echo "Found " . $layers . " layers!".$delimiter;

// Define some helper vars
$laserOff = $laserOffCode . $delimiter;
$laserOn = $laserOnCode . " S" . $laserPower . $delimiter;
if($syncForce) {
	$laserOff .= "M400" . $delimiter;
	$laserOn .= "M400" . $delimiter;
}
$laser = ON;
$layer = 0;

echo "Processing: ";

// Start the actual parse
rewind($handle);
while(!feof($handle) && @$l != "Stop") {
	$l = stream_get_line($handle, 0, $delimiter);
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
			file_put_contents($output, $l.$delimiter, FILE_APPEND);	// line, don't touch it, output as-is
		} else {
			$buffer = "";
			$l = explode(" ", $l);

			if($lineTypeFlags[IS_XY]) {				// If it's an XY move
				
				if(!$lineTypeFlags[HAS_E]) {		// If it's a jump move (no E)
					if($laser == ON)				// Turn laser off if on
						$buffer .= $laserOff;

					$buffer .= implode(" ", $l);	// Copy the jump move line

					if(!$lineTypeFlags[HAS_F]) 		// If it has no feedrate, set it
						$buffer .= " F" . number_format((float) $jumpSpeed, 3, ".", "");

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

				file_put_contents($output, $buffer, FILE_APPEND);
			
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
					$zLift = number_format($layerZ + (float) $peelHeight, 3, ".", "");

					$buffer .= $laserOff;			// Turn laser off
					$laser = OFF;

					$buffer .= "G1 E" . $zLift;		// Lift the right side
					$buffer .= " F" . number_format((float) $peelLiftSpeed, 3, ".", "");
					$buffer .= $delimiter;

					$buffer .= "G4 P250" . $delimiter;	// Wait a bit

					$buffer .= "G1 Z" . $zLift;		// Lift the left side
					$buffer .= " F" . number_format((float) $peelLiftSpeed, 3, ".", "");
					$buffer .= $delimiter;

					if($layer == 2)					// First peel has a longer pause
						$buffer .= "G4 P" . $basePause . $delimiter;
					else 							// Consecutive peels have a short pause
						$buffer .= "G4 P250" . $delimiter;
				}

				$buffer .= "G1 Z" . $layerZ;		// Lower the platform back 
				$buffer .= " E" . $layerZ;
				$buffer .= " F" . number_format((float) $peelDropSpeed, 3, ".", "");
				$buffer .= $delimiter;

				if($layer > 1) {					// First layer skips the extra laser logic
					$buffer .= "G4 P" . $peelPause . $delimiter;
				}

				file_put_contents($output, $buffer, FILE_APPEND);

				echo progressBar($layer, $layers, "30");
			} else {								// If it's anything else, copy it
				if(!$lineTypeFlags[HAS_E])			// Unless it's messing with the E axis
					file_put_contents($output, implode(" ",$l).$delimiter, FILE_APPEND);
			}
		}
	}
}

// Add the ending G code block
echo "\nDone.\n";
echo "Adding the G-code post-script code.\n";
file_put_contents($output, $endCode . $delimiter, FILE_APPEND);

