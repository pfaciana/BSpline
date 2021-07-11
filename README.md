BSpline
=======

A simple and fast uniform-knot BSpline curve implementation in PHP.
Usually, BSpline's basis function is defined recursively by De Boor's algorithm. But this library includes pre-calculated uniform-knot BSpline basis. Thus, the library works very fast.

Usage:

    $spline = new BSpline($points);
    $spline->calcAt($t, $degree);
    // or
    $spline->run($degree);

* *points* : The array of points. Array of any dimensional vector is OK.
* *degree* : The degree of BSpline curve. *degree* should be 2,3,4 or 5.
* *t* : The parametor of BSpline. t is in [0,1]. If t = 0 then returns first point of *points*. If t = 1 then returns last point of *points*.

Example:

    $points = [[1,2],[2,3],[3,4]];
    $spline = new BSpline($points);
    for($t = 0; $t <= 1; $t += 0.01) {
	    [$x, $y] = $spline->calcAt($t, 3);
    }
    // or
    $sPoints = $spline->run(3);

Demo is available [here](http://tagussan.rdy.jp/singleProjects/BSpline/ "Demo")

> **NOTE:** This is a PHP port of https://github.com/Tagussan/BSpline