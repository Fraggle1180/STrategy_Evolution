<?
include_once('common/models/move.php');

abstract class ctrStrategy	{
	protected $move_sequence;
	protected $player_side;
	protected $current_move;
	protected $param;

	function __construct(&$move_sequence, $player_side)	{
		$this->move_sequence	= &$move_sequence;
		$this->player_side	= $player_side;
		$this->current_move	= 0;

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
		switch ($lang)	{
			case 'ru':	{
				switch ($name)	{
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

					default:	throw new Exception("Unknown strategy code: $name");
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
		$move_number = $this->current_move-$ago;
		return $this->move_sequence[$move_number-1]->get('player'.$this->player_side.'_perception');
	}
};
