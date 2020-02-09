<?
include_once('str_copycat.php');

# Простак: начать отдаванием, если первый ход другого игрока - отдающий, то в дальнейшем повторять ходы другого игрока, иначе - делать противоположное другому игроку
class ctrStrategy_simpleton extends ctrStrategy_copycat {
	protected $bRevert;

	protected function MakeDecision()	{
		if ($this->current_move == 1)	return 1;

		if ($this->current_move == 2)	{
			$this->bRevert = ($this->getOtherSideLastMove() == 1) ? 0 : 1;
		}

		$last_res  = parent::MakeDecision();
		return $this->bRevert ? (1 - $last_res) : $last_res;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '478391';
	}
};
