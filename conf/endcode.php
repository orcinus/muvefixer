<?
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