<?php
function radcalc( $hyp, $opp, $adj )
{
	// sin(rad) = opp/hyp , rad = asin(opp/hyp)
	// cos(rad) = adj/hyp , rad = acos(adj/hyp)
	// tan(rad) = opp/adj , rad = atan(opp/adj)
	$sin = asin($opp / $hyp);
	$cos = acos($adj / $hyp);
	$tan = atan($opp / $adj);
	printf("sin() opp/hyp = %f rad\n", $sin);
	printf("cos() adj/hyp = %f rad\n", $cos);
	printf("tan() opp/adj = %f rad\n", $tan);
	return;
}

//   B  AB = 5 , rA = 36.87 deg or 0.64 rad
//  /|  AC = 4 , rB = 53.13 deg or 0.93 rad
// A-C  BC = 3 , rC = 90    deg or 1.57 rad
echo "== 3-4-5 triangle ==\n";
$AB = 5;
$AC = 4;
$BC = 3;

echo "rA = 0.64 rad\n";
radcalc($AB, $BC, $AC);

echo "rB = 0.93 rad\n";
radcalc($AB, $AC, $BC);

//echo "rC = 1.57 rad\n";
//radcalc($AB, $AB, $AC);

