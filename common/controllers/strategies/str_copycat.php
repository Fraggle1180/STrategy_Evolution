<?
include_once('common.php');

# Копировать: так же, как предыдущий ход другого игрока
class ctrStrategy_copycat extends ctrStrategy {
	protected function MakeDecision($player_side)	{
		return ($this->current_move[$player_side] == 1) ? 1 : $this->getOtherSideLastMove($player_side);
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '4ff6ff';
	}
};
