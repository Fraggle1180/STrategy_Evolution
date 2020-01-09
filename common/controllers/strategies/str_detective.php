<?
include_once('common.php');

class ctrStrategy_detective extends ctrStrategy {
	protected $bAlwaysTake;

	protected function MakeDecision()	{
		switch ($this->current_move)	{
			case 1:	return 1;
			case 2:	return 1;
			case 3:	return 0;
			case 4:	return 1;
		}

		if ($this->current_move == 5)	{
			$m1 = $this->getOtherSideMove(4);
			$m2 = $this->getOtherSideMove(3);
			$m3 = $this->getOtherSideMove(2);
			$m4 = $this->getOtherSideMove(1);

			$this->bAlwaysTake = (($m1 == 1) and ($m2 == 1) and ($m3 == 1) and ($m4 == 1)) ? 1 : 0;
		}

		return ($this->bAlwaysTake) ? 0 : $this->getOtherSideLastMove();
	}

	function setParam($param = null)	{
		return true;
	}
};
