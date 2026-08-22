<?php
// 3D texture quad-rendering + transformation related
//
// product = factor * factor
//             | x 0 0 |
// | x y z | = | 0 y 0 |
//             | 0 0 z |
// cross = 0 parallel      , 1 perpendicular
// dot   = 0 perpendicular , 1 parallel
//   perpendicular = 90 degree
//   parallel      = 0 or 180 degree (sin = 0)
//
//   dot(x,y) = |x||y| cos Q
// cross(x,y) = |x||y| sin Q

//////////////////////////////
//////////////////////////////
/*
	sum of all interior angles
		side  degree  radian
		3     180     pi      // tri
		4     360     2 pi    // quad
		5     540     3 pi    // penta
		6     720     4 pi    // hex
		...

	https://en.m.wikipedia.org/wiki/Trigonometric_functions
	-       90
	-     Q2 | Q1    Qref = Q1         sin Q rel Y value
	- 180 ------- 0  Qref = 180 - Q2   cos Q rel X value
	-     Q3 | Q4    Qref = Q3  - 180  tan Q is Y/X value
	-       270      Qref = 360 - Q4

	SOH  sine    = opposite / hypotenuse , cosecant  = hypotenuse / opposite
	CAH  cosine  = adjacent / hypotenuse , secant    = hypotenuse / adjacent
	TOA  tangent = opposite / adjacent   , cotangent = adjacent   / opposite
//////////////////////////////
 */
