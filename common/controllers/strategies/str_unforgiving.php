<?
include_once('common.php');

class ctrStrategy_unforgiving extends ctrStrategy {
	protected function MakeDecision()	{
		throw new Exception('not implemented');
		return -1;
	}
};
