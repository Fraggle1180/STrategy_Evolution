<?
include_once('common/models/move.php');

abstract class ctrStrategy	{
	protected $move_sequence;
	protected $move_sequence_type;
	protected $zero_move;
	protected $current_move;
	protected $param;

	function clear_move_sequence()	{
		$this->move_sequence		= null;
		$this->move_sequence_type	= null;
		$this->current_move		= null;
	}

	function set_move_sequence(&$move_sequence, $player_side)	{
		$this->move_sequence[$player_side]	= & $move_sequence;
		$this->move_sequence_type[$player_side]	= (gettype($this->move_sequence[$player_side]) == 'object') ? 'set' : 'array';

		$this->current_move[$player_side] = 0;
		$this->zero_move[$player_side]	  = ($this->move_sequence_type[$player_side] == 'set') ? $this->move_sequence[$player_side]->count() : count($this->move_sequence[$player_side]);
	}

	static function getClass_byName($name)	{
		switch ($name)	{
			case 'give':		return 'ctrStrategy_give';
			case 'take':		return 'ctrStrategy_take';
			case 'copycat':		return 'ctrStrategy_copycat';
			case 'unforgiving':	return 'ctrStrategy_unforgiving';
			case 'detective':	return 'ctrStrategy_detective';
			case 'random':		return 'ctrStrategy_random';
			case 'simpleton':	return 'ctrStrategy_simpleton';
			case 'copycat_forgiving':	return 'ctrStrategy_copycat_forgiving';
			case 'copycat_rebalance':	return 'ctrStrategy_copycat_rebalance';
			case 'copycat_trusted':		return 'ctrStrategy_copycat_trusted';

			default:	throw new Exception("Unknown strategy code: $name");
		}
	}

	static function translateName($name, $lang)	{
		$m  = array();
		$pm = preg_match('|(.+)\s+\((.*)\)|', $name, $m);

		$strat_name  = $pm ? $m[1] : $name;
		$strat_param = $pm ? $m[2] : '';

		switch ($lang)	{
			case 'ru':	{
				switch ($strat_name)	{
					case 'give':		return "Отдающий";
					case 'take':		return "Забирающий";
					case 'copycat':		return "Копирующий";
					case 'unforgiving':	return "Непрощающий";
					case 'detective':	return "Детектив";
					case 'random':		return "Случайный ($strat_param)";
					case 'simpleton':	return "Простак";
					case 'copycat_forgiving':	return "Копирующий с прощением ($strat_param)";
					case 'copycat_rebalance':	return "Копирующий с ребалансировкой ($strat_param)";
					case 'copycat_trusted':		return "Копирующий с доверием ($strat_param)";

					default:	throw new Exception("Unknown strategy code: $strat_name /full: $name/");
				}
			}

			default:	throw new Exception("Language $lang unknown or not supported");
		}
	}

	abstract protected function MakeDecision($player_side);
	abstract function setParam($param = null);
	abstract function getColor();

	function MakeMove($player_side)	{
		$this->current_move[$player_side]++;

		return $this->MakeDecision($player_side);
	}

	protected function getOtherSideLastMove($player_side)	{
		return $this->getOtherSideMove($player_side, 1);
	}

	protected function getOtherSideMove($player_side, $ago)	{
		return $this->getTheMove($player_side, $ago, 'perception');
	}

	protected function getMyLastMove($player_side)	{
		return $this->getMyMove($player_side, 1);
	}

	protected function getMyMove($player_side, $ago)	{
		return $this->getTheMove($player_side, $ago, 'decision');
	}

	protected function getTheMove($player_side, $ago, $part)	{
		$move_number = $this->zero_move[$player_side] + $this->current_move[$player_side] - $ago - 1;
		$move_key    = 'player'.$player_side.'_'.$part;

		if ($this->move_sequence_type[$player_side] == 'set')	{
			if (!$this->move_sequence[$player_side]->rec_exists($move_number))	throw new Exception("Move history unreacheable");
			return $this->move_sequence[$player_side]->get($move_number, $move_key);
		}	else	{
			if (!isset($this->move_sequence[$player_side][$move_number]))	throw new Exception("Move data unreacheable");
			return $this->move_sequence[$player_side][$move_number][$move_key];
		}
	}
};
