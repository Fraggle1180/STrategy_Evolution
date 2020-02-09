<?
include_once('common.php');

# Всегда забирать
class ctrStrategy_take extends ctrStrategy {
	protected function MakeDecision()	{
		return 0;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '040040';
	}
};
