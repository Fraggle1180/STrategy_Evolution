<?
include_once('common.php');

class ctrStrategy_give extends ctrStrategy {
	protected function MakeDecision()	{
		return 1;
	}

	function setParam($param = null)	{
		return true;
	}
};
