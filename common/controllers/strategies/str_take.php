<?
include_once('common.php');

class ctrStrategy_take extends ctrStrategy {
	protected function MakeDecision()	{
		return 0;
	}

	function setParam($param = null)	{
		return true;
	}
};
