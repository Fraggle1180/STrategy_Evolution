<?
include_once('common.php');

class ctrStrategy_copycat extends ctrStrategy {
	protected function MakeDecision()	{
var_dump($this->current_move);
var_dump($this->move_sequence);
		if ($this->current_move == 1)	return 1;

		throw new Exception('not implemented');
		return -1;
	}
};
