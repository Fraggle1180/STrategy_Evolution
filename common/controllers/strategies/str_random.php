<?
include_once('common.php');

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
};
