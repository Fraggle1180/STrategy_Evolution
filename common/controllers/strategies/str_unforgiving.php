<?
include_once('common.php');

# Непрощающий: отдавать до первого обмана с другой стороны, затем - забирать
class ctrStrategy_unforgiving extends ctrStrategy {
	protected $wasBetrayed;

	protected function MakeDecision($player_side)	{
		if ($this->current_move[$player_side] == 1)	{
			$this->wasBetrayed = 0;
		}	else	{
			if ($this->getOtherSideLastMove($player_side) == 0)
				$this->wasBetrayed = 1;
		}

		return $this->wasBetrayed ? 0 : 1;
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return '813000';
	}
};
