<?
// *** HELPER FUNCTIONS ***
// ************************

// Checks if $what or (array) $what is present in $l
function inLine($l, $what, $andMode = FALSE) {
	if(!is_array($what)) {					// Comparing to a string
		if(strpos($l, $what) !== FALSE)
			return TRUE;
	} else {								// Comparing to an array of strings
		// Sanity checks
		if(count($what) == 0)
			throw new Exception("ERROR - inLine comparison array empty!");
		foreach($what as $w) {
			if($w == "")
				throw new Exception("ERROR - inLine can't compare to nothing!");				
		}

		if($andMode == FALSE) {				// Return TRUE if either of the $whats is found
			foreach($what as $w) {
				if(strpos($l, $w) !== FALSE)
					return TRUE;
			}
		} else {							// Return TRUE if all of the $whats are found
			$return = FALSE;
			foreach($what as $w) {
				if(strpos($l, $w) !== FALSE)
					$return = $return AND TRUE;
				else
					$return = $return AND FALSE;
			}
			return $return;
		}
	}

	return FALSE;
}

// Detects end of line delimiter
function detectDelimiter($handle) {
	$newLineTest = fgets($handle);

	if(inLine($newLineTest, "\r")) {
		if(substr($newLineTest, strlen($newLineTest) - 1, 1) == "\n")
			$delimiter = "\r\n";
		else
			$delimiter = "\r";
	} else {
		$delimiter = "\n";
	}

	return $delimiter;
}

// Counts the Z layers
function countLinesWithZ($handle, $delimiter) {
	$layers = 0;

	while(!feof($handle)) {
		$l = stream_get_line($handle, 0, $delimiter);
		if(substr($l, 0, 1) == ";" || substr($l, 0, 1) == substr($delimiter, 0, 1))
			continue;
		else
			if(inLine($l, " Z") !== FALSE && !inLine($l, " E"))
				$layers++;
	}

	return $layers;
}

// Explode path into path, filename and extension
function explodePath($path) {
	preg_match("#^(.+/)*(.*)\.(.*)#", $path, $return);
	unset($return[0]);
	return array_values($return);
}

// Progress bar generator
function progressBar($current, $total, $length) {
	echo " [0%";
	echo "\033[" . ($length + 2) . "C";
	echo " 100%]";
	echo "\033[" . ($length + 7) . "D";

	$position = (int) (($current/$total)*$length);
	if($position)
		for($i=0; $i < $position; $i++) { 
			echo "=>";
			echo "\033[1D";
		}

	echo "\033[" . ($position + 5). "D";
}