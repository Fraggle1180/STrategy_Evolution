<?
include_once('str_copycat.php');

# Копировать с прощением: так же, как лучший из N предыдущих ходов другого игрока
class ctrStrategy_copycat_forgiving extends ctrStrategy_copycat {
	protected $remember_moves_number;

	protected function MakeDecision()	{
		for( $best_move = null, $n = 1;	$n <= $this->remember_moves_number; $n++ )	{
			try { $move = $this->getOtherSideMove($n); }
			catch (Exception $e) { continue; }

			$best_move = (is_null($best_move)) ? $move : max($best_move, $move);
		}

		return is_null($best_move) ? 1 : $best_move;
	}

	function setParam($param = null)	{
		if (is_numeric($param))	{
			$this->remember_moves_number = $param;
			return true;
		}

		if (is_null($param))	{
			$this->remember_moves_number = 2;
			return true;
		}

		return false;
	}
};
