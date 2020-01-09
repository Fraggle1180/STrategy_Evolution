<?
include_once('common.php');

# Непрощающий: отдавать до первого обмана с другой стороны, затем - забирать
class ctrStrategy_unforgiving extends ctrStrategy {
	protected $wasBetrayed;

	protected function MakeDecision()	{
		if ($this->current_move == 1)	{
			$this->wasBetrayed = 0;
		}	else	{
			if ($this->getOtherSideLastMove() == 0)
				$this->wasBetrayed = 1;
		}

		return $this->wasBetrayed ? 0 : 1;
	}

	function setParam($param = null)	{
		return true;
	}
};
