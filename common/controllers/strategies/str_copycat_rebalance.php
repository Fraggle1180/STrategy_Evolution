<?
include_once('str_copycat.php');

# Копировать с ребалансировкой: если идёт синусоида длиной не меньше N - сделать отдающий ход
class ctrStrategy_copycat_rebalance extends ctrStrategy_copycat {
	protected $rebalance_moves_number;

	protected function MakeDecision()	{
		if ($this->current_move <= $this->rebalance_moves_number)	return parent::MakeDecision();


		# проверить возникновение синусоиды
		for( $moves = array(), $n = 1;	$n < $this->rebalance_moves_number; $n++ )	{
			$my_move	= $this->getMyMove($n);
			$other_move	= $this->getOtherSideMove($n);

			$kind = 'unknown';
			if (($my_move == 0) and ($other_move == 1)) $kind = 'cheat';
			if (($my_move == 1) and ($other_move == 0)) $kind = 'loose';

			$move[] = array( 0 => $my_move, 1 => $other_move, 'kind' => $kind );
		}

		$cases = array( 'sinus_neg' => 0, 'sinus_pos' => 0, 'other' => 0 );
		foreach( $moves as $num => $move )	{
			$odd = ($num % 2 <> 0);

			$key = 'other';
			if (( $odd and ($move['kind'] == 'cheat')) or (!$odd and ($move['kind'] == 'loose')))	$key = 'sinus_neg';
			if ((!$odd and ($move['kind'] == 'cheat')) or ( $odd and ($move['kind'] == 'loose')))	$key = 'sinus_pos';

			$cases[$key]++;
		}

		$isSinus = (($cases['other'] == 0) and ((($cases['sinus_neg'] > 0) and ($cases['sinus_pos'] == 0)) or (($cases['sinus_neg'] == 0) and ($cases['sinus_pos'] > 0))));


		# если идёт синусоида - сделать отдающий ход
		if ($isSinus)	return 1;


		# иначе - обычное копирование
		return parent::MakeDecision();
	}

	function setParam($param = null)	{
		if (is_numeric($param))	{
			$this->rebalance_moves_number = $param;
			return true;
		}

		if (is_null($param))	{
			$this->rebalance_moves_number = 2;
			return true;
		}

		return false;
	}

	function getColor()	{
		$r =  37;
		$g = 202;
		$b = 224;

		$d = ($this->rebalance_moves_number > 0) ? round(log($this->rebalance_moves_number, 2), 0) : 0;

		$dr = 1;
		$dg = 3;
		$db = 3;

		$fr = $r + $d * $dr;
		$fg = $g + $d * $dg;
		$fb = $b + $d * $db;


		return substr('0'.dechex($fr), -2, 2) . substr('0'.dechex($fg), -2, 2) . substr('0'.dechex($fb), -2, 2);
	}
};
