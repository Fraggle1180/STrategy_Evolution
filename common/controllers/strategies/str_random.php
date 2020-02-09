<?
include_once('common.php');

# Рандом
class ctrStrategy_random extends ctrStrategy {
	protected $random_level;

	protected function MakeDecision()	{
		if (rand(1, 100) <= $this->random_level)	return 0;
		return 1;
	}

	function setParam($param = null)	{
		if (is_numeric($param))	{
			$this->random_level = $param;
			return true;
		}

		if (is_null($param))	{
			$this->random_level = 50;
			return true;
		}

		return false;
	}

	function getColor()	{
		$r = 116;
		$g = 250;
		$b =  30;

		$above = ($this->random_level >= 50);

		$dr = ($above) ?  2.317 :  1.867;
		$dg = ($above) ? -1.300 :  4.167;
		$db = ($above) ?  3.383 : -0.567;

		$fr = round($r + ($this->random_level - 50) * $dr, 0);
		$fg = round($g + ($this->random_level - 50) * $dg, 0);
		$fb = round($b + ($this->random_level - 50) * $db, 0);


		return substr('0'.dechex($fr), -2, 2) . substr('0'.dechex($fg), -2, 2) . substr('0'.dechex($fb), -2, 2);
	}
};
