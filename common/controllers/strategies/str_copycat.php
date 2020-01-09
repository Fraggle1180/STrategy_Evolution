<?
include_once('common.php');

# Копировать: так же, как предыдущий ход другого игрока
class ctrStrategy_copycat extends ctrStrategy {
	protected function MakeDecision()	{
		return ($this->current_move == 1) ? 1 : $this->getOtherSideLastMove();
	}

	function setParam($param = null)	{
		return true;
	}
};
