<?
// *** DEFAULTS ***
// ****************

$output = 'test/test-mUVe.gcode';

$config["peelHeight"] = "2";			// mm
$config["peelPause"] = "500";			// ms
$config["peelLiftSpeed"] = "64";		// feedrate
$config["peelDropSpeed"] = "64";		// feedrate

$config["jumpSpeed"] = "5400";		// feedrate

$config["basePause"] = "10000";		// ms

$config["laserOnCode"] = "M106";
$config["laserOffCode"] = "M107";
$config["laserPower"] = "255";

$config["syncForce"] = TRUE;


// *** ENDING GCODE BLOCK ***
// **************************

$endCode = "
M107 ; laser off
M400 ; synchronize
G28 X0.000 Y0.000 ; home X and Y axis
G91 ; relative mode
G1 Z50.000 E50.000 F100.000 ; Z axis up 10
G90 ; absolute mode
M84 ; disable motors";
