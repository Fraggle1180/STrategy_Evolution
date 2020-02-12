<?
include_once('common.php');

# Всегда забирать
class ctrStrategy_take extends ctrStrategy {
	protected function MakeDecision($player_side)	{
		return 0;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '040040';
	}
};
