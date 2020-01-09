<?
include_once('common.php');

class ctrStrategy_simpleton extends ctrStrategy {
	protected $bRevert;

	protected function MakeDecision()	{
		if ($this->current_move == 1)	return 1;

		if ($this->current_move == 2)	{
			$this->bRevert = ($this->getOtherSideLastMove() == 1) ? 0 : 1;
		}

		$last_res  = $this->getOtherSideLastMove();
		return $this->bRevert ? (1 - $last_res) : $last_res;
	}

	function setParam($param = null)	{
		return true;
	}
};
