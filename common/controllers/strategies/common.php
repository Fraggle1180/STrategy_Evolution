<?
include_once('common/models/move.php');

abstract class ctrStrategy	{
	protected $move_sequence;
	protected $move_sequence_type;
	protected $player_side;
	protected $zero_move;
	protected $current_move;
	protected $param;

	function __construct(&$move_sequence, $player_side)	{
		$this->move_sequence	= &$move_sequence;
		$this->move_sequence_type = (gettype($this->move_sequence) == 'object') ? 'set' : 'array';

		$this->player_side	= $player_side;

		$this->current_move	= 0;
		$this->zero_move	= ($this->move_sequence_type == 'set') ? $this->move_sequence->count() : count($this->move_sequence);

		$this->setParam();
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
		$m = array();
		$strat_name = (preg_match('|(.+)\s+\(.*\)|', $name, $m)) ? $m[1] : $name;

		switch ($lang)	{
			case 'ru':	{
				switch ($strat_name)	{
					case 'give':		return 'Отдающий';
					case 'take':		return 'Забирающий';
					case 'copycat':		return 'Копирующий';
					case 'unforgiving':	return 'Непрощающий';
					case 'detective':	return 'Детектив';
					case 'random':		return 'Случайный';
					case 'simpleton':	return 'Простак';
					case 'copycat_forgiving':	return 'Копирующий с прощением';
					case 'copycat_rebalance':	return 'Копирующий с ребалансировкой';
					case 'copycat_trusted':		return 'Копирующий с доверием';

					default:	throw new Exception("Unknown strategy code: $strat_name /full: $name/");
				}
			}

			default:	throw new Exception("Language $lang unknown or not supported");
		}
	}

	abstract protected function MakeDecision();
	abstract function setParam($param = null);

	function MakeMove()	{
		$this->current_move++;

		return $this->MakeDecision();
	}

	protected function getOtherSideLastMove()	{
		return $this->getOtherSideMove(1);
	}

	protected function getOtherSideMove($ago)	{
		return $this->getTheMove($ago, 'perception');
	}

	protected function getMyLastMove()	{
		return $this->getMyMove(1);
	}

	protected function getMyMove($ago)	{
		return $this->getTheMove($ago, 'decision');
	}

	protected function getTheMove($ago, $part)	{
		$move_number = $this->zero_move + $this->current_move - $ago - 1;
		$move_key    = 'player'.$this->player_side.'_'.$part;

		if ($this->move_sequence_type == 'set')	{
			if (!$this->move_sequence->rec_exists($move_number))	throw new Exception("Move history unreacheable");
			return $this->move_sequence->get($move_number, $move_key);
		}	else	{
			if (!isset($this->move_sequence[$move_number]))	throw new Exception("Move data unreacheable");
			return $this->move_sequence[$move_number][$move_key];
		}
	}
};
