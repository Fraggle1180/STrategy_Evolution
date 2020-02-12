<?
include_once('str_copycat.php');

# Простак: начать отдаванием, если первый ход другого игрока - отдающий, то в дальнейшем повторять ходы другого игрока, иначе - делать противоположное другому игроку
class ctrStrategy_simpleton extends ctrStrategy_copycat {
	protected $bRevert;

	protected function MakeDecision($player_side)	{
		if ($this->current_move[$player_side] == 1)	return 1;

		if ($this->current_move[$player_side] == 2)	{
			$this->bRevert = ($this->getOtherSideLastMove($player_side) == 1) ? 0 : 1;
		}

		$last_res  = parent::MakeDecision($player_side);
		return $this->bRevert ? (1 - $last_res) : $last_res;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '478391';
	}
};
