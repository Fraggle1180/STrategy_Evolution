<?
include_once('str_copycat.php');

# Копировать с прощением: так же, как лучший из N предыдущих ходов другого игрока
class ctrStrategy_copycat_forgiving extends ctrStrategy_copycat {
	protected $remember_moves_number;

	protected function MakeDecision($player_side)	{
		for( $best_move = null, $n = 1;	$n <= $this->remember_moves_number; $n++ )	{
			try { $move = $this->getOtherSideMove($player_side, $n); }
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

	function getColor()	{
		$r =  37;
		$g = 202;
		$b = 224;

		$d = ($this->remember_moves_number > 0) ? round(log($this->remember_moves_number, 2), 0) : 0;

		$dr = 1;
		$dg = 3;
		$db = 3;

		$fr = $r + $d * $dr;
		$fg = $g + $d * $dg;
		$fb = $b + $d * $db;


		return strval(substr('0'.dechex($fr), -2, 2) . substr('0'.dechex($fg), -2, 2) . substr('0'.dechex($fb), -2, 2));
	}
};
