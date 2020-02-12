<?
include_once('common.php');

# Всегда отдавать
class ctrStrategy_give extends ctrStrategy {
	protected function MakeDecision($player_side)	{
		return 1;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return 'fface9';
	}
};
