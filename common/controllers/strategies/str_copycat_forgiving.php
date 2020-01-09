<?
include_once('common.php');

class ctrStrategy_copycat_forgiving extends ctrStrategy {
	protected $remember_moves_number;

	protected function MakeDecision()	{
		throw new Exception('not implemented');
		return -1;
	}

	function setParam($param = null)	{
		if (is_numeric($param))	{
			$this->remember_moves_number = $param;
			return true;
		}

		if (is_null($param))	{
			$this->remember_moves_number = 2;
			return true;
		}

		return false;
	}
};
