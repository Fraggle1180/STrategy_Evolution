<?
include_once('strategies.php');
include_once('common/models/move.php');
include_once('common/models/game.php');
include_once('common/models/tour.php');
include_once('common/models/player_in_tournament.php');

class ctrTour	{
	protected $param;
	protected $players;
	protected $data;

	function __construct()	{
		$this->param	= array( 'price1' => null, 'price2' => null, 'result1' => null, 'result2' => null, 'noise_in' => null, 'noise_out' => null, 'gamelen' => null );
		$this->players	= array( 'max_num' => 0 );
		$this->data	= array( );
	}

	function set_param($param, $value)	{
		if (!array_key_exists($param, $this->param))	return false;

		$this->param[$param] = $value;
		return true;
	}

	function set_player($num, $strat, $param)	{
		if ($num > $this->players['max_num'])	$this->players['max_num'] = $num;

		$this->players[$num] = array( 'strategy' => $strat, 'params' => $param, 'total_score' => 0 );
		return true;
	}

	function check_params_enough()	{
		foreach( $this->param as $value )
			if (is_null($value))
				return false;

		return true;
	}

	function run()	{
		$mod_tour = new modTour();

		$mod_tour->set('game_length',	$this->param['gamelen']);
		$mod_tour->set('price1',	$this->param['price1']);
		$mod_tour->set('price2',	$this->param['price2']);
		$mod_tour->set('result1',	$this->param['result1']);
		$mod_tour->set('result2',	$this->param['result2']);
		$mod_tour->set('noise_in',	$this->param['noise_in']);
		$mod_tour->set('noise_out',	$this->param['noise_out']);

		$mod_tour->Save();

		$this->data['tour_id'] = $mod_tour->get('id');


		for( $p = 1; $p <= $this->players['max_num']; $p++ )	{
			$this->players[$p]['total_score'] = 0;
		}


		for( $p1 = 1; $p1 <= $this->players['max_num']; $p1++ )	{
			if (is_null($this->players[$p1]))	continue;

			for( $p2 = 1; $p2 <= $this->players['max_num']; $p2++ )	{
				if (is_null($this->players[$p2]))	continue;

				$game_res = $this->run_game($p1, $p2);

				$this->players[$p1]['total_score'] += $game_res['player1']['score'];
				$this->players[$p2]['total_score'] += $game_res['player2']['score'];
			}
		}
	}

	function get_results()	{
		var_dump($this); die();

		return 'to be ...';
	}


	protected function run_game($pl1, $pl2)	{
		$p_price1	= $this->param['price1'];
		$p_price2	= $this->param['price2'];
		$p_result1	= $this->param['result1'];
		$p_result2	= $this->param['result2'];
		$p_noise_in	= $this->param['noise_in'];
		$p_noise_out	= $this->param['noise_out'];
		$p_gamelen	= $this->param['gamelen'];


		$mod_game = new modGame();
		$mod_game->Save();

		$move_sequence = array();

		$player1	= $this->players[$pl1];
		$str_class1	= ctrStrategy::getClass_byName($player1['strategy']);
		$pl_strategy1	= new $str_class1($move_sequence, 1);

		$player2	= $this->players[$pl2];
		$str_class2	= ctrStrategy::getClass_byName($player2['strategy']);
		$pl_strategy2	= new $str_class2($move_sequence, 2);


		for ( $m = 1; $m <= $p_gamelen; $m++ )	{
			$move = new modMove;

			$move_player1 = $pl_strategy1->MakeMove();
			$move_player2 = $pl_strategy2->MakeMove();


			$move_sequence[] = $move;
		}

		return array( 'player1' => array( 'score' => -1 ), 'player2' => array( 'score' => -1 ) );
	}
};
