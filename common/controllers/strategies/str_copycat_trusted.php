<?
include_once('common.php');

# Копировать с доверием: если создано доверие (из N предыдущих ходов другого игрока не менее M% были отдающими), то использовать копирование с ребалансировкой длины L, иначе - копирование
class ctrStrategy_copycat_trusted extends ctrStrategy {
	protected $trust_period;
	protected $trust_level;
	protected $rebalance_moves_number;

	protected function MakeDecision()	{
		throw new Exception('not implemented');
		return -1;
	}

	function setParam($param = null)	{
		if (is_null($param))	{
			$this->trust_period	= 5;
			$this->trust_level	= 80;
			$this->rebalance_moves_number = 5;
			return true;
		}

		if (is_numeric($param))	{
			$m = array();
			if (!preg_match('|(\d+);(\d+);(\d+)|', $param, $m))	return false;

			$this->trust_period	= $m[1];
			$this->trust_level	= $m[2];
			$this->rebalance_moves_number = $m[3];

			return true;
		}

		return false;
	}
};
