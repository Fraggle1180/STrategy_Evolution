<?
include_once('common/models/move.php');

abstract class ctrStrategy	{
	protected $move_sequence;
	protected $player_side;
	protected $current_move;

	function __construct(&$move_sequence, $player_side)	{
		$this->move_sequence	= &$move_sequence;
		$this->player_side	= &$player_side;
		$this->current_move	= 0;
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

	abstract protected function MakeDecision();

	function MakeMove()	{
		$this->current_move++;

		return $this->MakeDecision();
	}
};
