<?
include_once('str_copycat.php');

# Детектив: ОЗОО, затем - если были забирания, то копировать, если не было забираний, то всегда забирать
class ctrStrategy_detective extends ctrStrategy_copycat {
	protected $bAlwaysTake;

	protected function MakeDecision($player_side)	{
		switch ($this->current_move[$player_side])	{
			case 1:	return 1;
			case 2:	return 1;
			case 3:	return 0;
			case 4:	return 1;
		}

		if ($this->current_move == 5)	{
			$m1 = $this->getOtherSideMove($player_side, 4);
			$m2 = $this->getOtherSideMove($player_side, 3);
			$m3 = $this->getOtherSideMove($player_side, 2);
			$m4 = $this->getOtherSideMove($player_side, 1);

			$this->bAlwaysTake = (($m1 == 1) and ($m2 == 1) and ($m3 == 1) and ($m4 == 1)) ? 1 : 0;
		}

		return ($this->bAlwaysTake) ? 0 : parent::MakeDecision($player_side);
	}

	function setParam($param = null)	{
		return true;
	}

	function getColor()	{
		return 'ffd46d';
	}
};
