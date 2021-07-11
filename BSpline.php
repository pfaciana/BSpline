<?php

class BSpline
{
	protected $points;
	protected $dimension;
	protected $degree;
	protected $rangeInt;

	public function __construct ($points,)
	{
		$this->points    = $points;
		$this->dimension = count($points[0]);
	}

	public function run ($degree, $nPoints = 1000)
	{
		$smoothPoints = [];

		$last = NULL;
		for ($t = 0; $t <= 1; $t += (1 / $nPoints)) {
			$current = $this->calcAt($t, $degree);
			$xLookup = $this->points[count($smoothPoints)][0];

			if ($current[0] == $xLookup) {
				$smoothPoints[] = $current;
			}
			elseif (!empty($last)) {
				if ($last[0] < $xLookup && $xLookup < $current[0]) {
					$smoothPoints[] = $this->getPointOnASlope($xLookup, $last, $current);
				}
			}

			if (count($smoothPoints) >= count($this->points)) {
				break;
			}

			$last = $current;
		}

		return $smoothPoints;
	}

	protected function getYOnASlope ($x, $xyA, $xyB)
	{
		$m = ($xyA[1] - $xyB[1]) / ($xyA[0] - $xyB[0]);
		$b = $xyA[1] - $m * $xyA[0];

		return ($x * $m) + $b;
	}

	protected function getPointOnASlope ($x, $xyA, $xyB)
	{
		return [$x, $this->getYOnASlope($x, $xyA, $xyB)];
	}

	public function getInterpol ($seq, $t)
	{
		$basisDeg = 'basisDeg' . $this->degree;
		$rangeInt = $this->rangeInt;
		$tInt     = floor($t);
		$result   = 0;
		for ($i = $tInt - $rangeInt; $i <= $tInt + $rangeInt; $i++) {
			$result += $seq($i) * $this->$basisDeg($t - $i);
		}

		return $result;
	}

	public function seqAt ($dim)
	{
		return function ($n) use ($dim) {
			$points = $this->points;
			$margin = $this->degree + 1;

			if ($n < $margin) {
				return $points[0][$dim];
			}

			if (count($points) + $margin <= $n) {
				return $points[count($points) - 1][$dim];
			}

			return $points[$n - $margin][$dim];
		};
	}

	public function calcAt ($t, $degree)
	{
		$this->degree   = $degree;
		$this->rangeInt = $degree <= 3 ? 2 : 3;

		$t = $t * (($this->degree + 1) * 2 + count($this->points)); // $t must be in [0,1]

		$res = [];
		for ($i = 0; $i < $this->dimension; $i++) {
			$res[] = $this->getInterpol($this->seqAt($i), $t);
		}

		return $res;
	}

	public function basisDeg2 ($x)
	{
		if (-0.5 <= $x && $x < 0.5) {
			return 0.75 - $x * $x;
		}
		if (0.5 <= $x && $x <= 1.5) {
			return 1.125 + (-1.5 + $x / 2.0) * $x;
		}
		if (-1.5 <= $x && $x < -0.5) {
			return 1.125 + (1.5 + $x / 2.0) * $x;
		}

		return 0;
	}

	public function basisDeg3 ($x)
	{
		if (-1 <= $x && $x < 0) {
			return 2.0 / 3.0 + (-1.0 - $x / 2.0) * $x * $x;
		}
		if (1 <= $x && $x <= 2) {
			return 4.0 / 3.0 + $x * (-2.0 + (1.0 - $x / 6.0) * $x);
		}
		if (-2 <= $x && $x < -1) {
			return 4.0 / 3.0 + $x * (2.0 + (1.0 + $x / 6.0) * $x);
		}
		if (0 <= $x && $x < 1) {
			return 2.0 / 3.0 + (-1.0 + $x / 2.0) * $x * $x;
		}

		return 0;
	}

	public function basisDeg4 ($x)
	{
		if (-1.5 <= $x && $x < -0.5) {
			return 55.0 / 96.0 + $x * (-(5.0 / 24.0) + $x * (-(5.0 / 4.0) + (-(5.0 / 6.0) - $x / 6.0) * $x));
		}
		if (0.5 <= $x && $x < 1.5) {
			return 55.0 / 96.0 + $x * (5.0 / 24.0 + $x * (-(5.0 / 4.0) + (5.0 / 6.0 - $x / 6.0) * $x));
		}
		if (1.5 <= $x && $x <= 2.5) {
			return 625.0 / 384.0 + $x * (-(125.0 / 48.0) + $x * (25.0 / 16.0 + (-(5.0 / 12.0) + $x / 24.0) * $x));
		}
		if (-2.5 <= $x && $x <= -1.5) {
			return 625.0 / 384.0 + $x * (125.0 / 48.0 + $x * (25.0 / 16.0 + (5.0 / 12.0 + $x / 24.0) * $x));
		}
		if (-1.5 <= $x && $x < 1.5) {
			return 115.0 / 192.0 + $x * $x * (-(5.0 / 8.0) + $x * $x / 4.0);
		}

		return 0;
	}

	public function basisDeg5 ($x)
	{
		if (-2 <= $x && $x < -1) {
			return 17.0 / 40.0 + $x * (-(5.0 / 8.0) + $x * (-(7.0 / 4.0) + $x * (-(5.0 / 4.0) + (-(3.0 / 8.0) - $x / 24.0) * $x)));
		}
		if (0 <= $x && $x < 1) {
			return 11.0 / 20.0 + $x * $x * (-(1.0 / 2.0) + (1.0 / 4.0 - $x / 12.0) * $x * $x);
		}
		if (2 <= $x && $x <= 3) {
			return 81.0 / 40.0 + $x * (-(27.0 / 8.0) + $x * (9.0 / 4.0 + $x * (-(3.0 / 4.0) + (1.0 / 8.0 - $x / 120.0) * $x)));
		}
		if (-3 <= $x && $x < -2) {
			return 81.0 / 40.0 + $x * (27.0 / 8.0 + $x * (9.0 / 4.0 + $x * (3.0 / 4.0 + (1.0 / 8.0 + $x / 120.0) * $x)));
		}
		if (1 <= $x && $x < 2) {
			return 17.0 / 40.0 + $x * (5.0 / 8.0 + $x * (-(7.0 / 4.0) + $x * (5.0 / 4.0 + (-(3.0 / 8.0) + $x / 24.0) * $x)));
		}
		if (-1 <= $x && $x < 0) {
			return 11.0 / 20.0 + $x * $x * (-(1.0 / 2.0) + (1.0 / 4.0 + $x / 12.0) * $x * $x);
		}

		return 0;
	}
}