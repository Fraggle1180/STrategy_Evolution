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
	protected $stats;

	function __construct()	{
		$this->param	= array( 'price1' => null, 'price2' => null, 'result1' => null, 'result2' => null, 'noise_in' => null, 'noise_out' => null, 'gamelen' => null );
		$this->players	= array( 'max_num' => 0 );
		$this->data	= array( );
		$this->stats	= array( 'time' => null, 'players' => 0, 'games' => 0, 'moves' => 0 );
	}

	function set_param($param, $value)	{
		if (!array_key_exists($param, $this->param))	return false;

		$this->param[$param] = $value;
		return true;
	}

	function set_player($num, $strat, $param)	{
		if ($num > $this->players['max_num'])	$this->players['max_num'] = $num;

		$this->players[$num] = array( 'strategy' => $strat, 'params' => $param );
		return true;
	}

	function check_params_enough()	{
		foreach( $this->param as $value )
			if (is_null($value))
				return false;

		return true;
	}

	function run()	{
		$this->stats['time'] = time();

		$mod_tour = new modTour();

		$mod_tour->set('game_length',	$this->param['gamelen']);
		$mod_tour->set('price1',	$this->param['price1']);
		$mod_tour->set('price2',	$this->param['price2']);
		$mod_tour->set('result1',	$this->param['result1']);
		$mod_tour->set('result2',	$this->param['result2']);
		$mod_tour->set('noise_in',	$this->param['noise_in']);
		$mod_tour->set('noise_out',	$this->param['noise_out']);

		$mod_tour->save();

		$this->data['tour_id'] = $mod_tour->get('id');


		for( $p = 1; $p <= $this->players['max_num']; $p++ )	{
			if (!array_key_exists($p, $this->players) or is_null($this->players[$p]))	continue;

			$this->stats['players']++;

			$this->players[$p]['model'] = new modPlayerInTournament;

			$st = $this->players[$p]['strategy'];
			$pr = $this->players[$p]['params'];
			$db = $st . ((is_null($pr) or !$pr) ? '' : " ($pr)");

			$this->players[$p]['model']->set('id_tournament', $this->data['tour_id']);
			$this->players[$p]['model']->set('player_number', $p);
			$this->players[$p]['model']->set('player_strategy', $db);
			$this->players[$p]['model']->set('player_result', 0);

			$this->players[$p]['model']->save();
		}


		for( $p1 = 1; $p1 <= $this->players['max_num']; $p1++ )	{
			if (!array_key_exists($p1, $this->players) or is_null($this->players[$p1]))	continue;

			for( $p2 = 1; $p2 <= $this->players['max_num']; $p2++ )	{
				if (!array_key_exists($p2, $this->players) or is_null($this->players[$p2]))	continue;

				$this->run_game($p1, $p2);
			}
		}

		for( $p = 1; $p <= $this->players['max_num']; $p++ )	{
			if (!array_key_exists($p, $this->players) or is_null($this->players[$p]))	continue;

			$this->players[$p]['model']->save();
		}


		$this->stats['time'] = time() - $this->stats['time'];
	}

	function get_results()	{
		return array( 'rating' => modPlayerInTournament::getRating($this->data['tour_id']), 'stats' => $this->stats );
	}


	protected function run_game($pl1, $pl2)	{
		set_time_limit(300);

		$this->stats['games']++;


		# параметры очередной игры
		$p_price1	= $this->param['price1'];
		$p_price2	= $this->param['price2'];
		$p_result1	= $this->param['result1'];
		$p_result2	= $this->param['result2'];
		$p_noise_in	= $this->param['noise_in'];
		$p_noise_out	= $this->param['noise_out'];
		$p_gamelen	= $this->param['gamelen'];
		$d_tour_id	= $this->data['tour_id'];


		# две стороны игры
		$player1	= $this->players[$pl1];
		$str_class1	= ctrStrategy::getClass_byName($player1['strategy']);
		$pl_strategy1	= new $str_class1($move_sequence, 1);
		$pl_strategy1->setParam($player1['params']);
		$mod_payer1	= &$this->players[$pl1]['model'];

		$player2	= $this->players[$pl2];
		$str_class2	= ctrStrategy::getClass_byName($player2['strategy']);
		$pl_strategy2	= new $str_class2($move_sequence, 2);
		$pl_strategy1->setParam($player1['params']);
		$mod_payer2	= &$this->players[$pl2]['model'];


		# модель данных игры
		$mod_game = new modGame();

		$mod_game->set('id_tournament',	$d_tour_id);
		$mod_game->set('start_at',	time());
		$mod_game->set('player1_number', $pl1);
		$mod_game->set('player2_number', $pl2);
		$mod_game->set('player1_strategy',	$mod_payer1->get('player_strategy'));
		$mod_game->set('player2_strategy',	$mod_payer2->get('player_strategy'));
		$mod_game->set('player1_result', 0);
		$mod_game->set('player2_result', 0);

		$mod_game->save();

		$d_game_id	= $mod_game->get('id');


		# моделировать игру
		# результат: в моделях игроков изменятся player_score в соотвествии с результатом этой игры
		$move_sequence = new modMove;

		$profiler = new fsb_profiler;
		$profiler_tick_param = "($pl1 vs $pl2) s1: ".$player1['strategy'].", s2: ".$player2['strategy'].", m: $p_gamelen";
		$profiler->Tick('ctrTour::game', $profiler_tick_param);

		for ( $m = 1; $m <= $p_gamelen; $m++ )	{
			$this->stats['moves']++;

			$p1_decision = $pl_strategy1->MakeMove();
			$p2_decision = $pl_strategy2->MakeMove();

			$p1_action   = (rand(1, 100) > $p_noise_in) ? $p1_decision : (1 - $p1_decision);
			$p2_action   = (rand(1, 100) > $p_noise_in) ? $p2_decision : (1 - $p2_decision);

			$p1_result   = - ($p1_action * $p_price1) + $p2_action * $p_result1;
			$p2_result   = - ($p2_action * $p_price2) + $p1_action * $p_result2;

			$p1_perception = (rand(1, 100) > $p_noise_out) ? $p2_action : (1 - $p2_action);
			$p2_perception = (rand(1, 100) > $p_noise_out) ? $p1_action : (1 - $p1_action);


			$move_ind = $move_sequence->add();
			$move_sequence->set_bulk( $move_ind, array( 'id_game' => $d_game_id, 'number_move' => $m, 'player1_decision' => $p1_decision, 'player2_decision' => $p2_decision, 'player1_action' => $p1_action, 'player2_action' => $p2_action, 'player1_perception' => $p1_perception, 'player2_perception' => $p2_perception) );


			$mod_payer1->set('player_result', $mod_payer1->get('player_result') + $p1_result);
			$mod_payer2->set('player_result', $mod_payer2->get('player_result') + $p2_result);

			$mod_game->set('player1_result', $mod_game->get('player1_result') + $p1_result);
			$mod_game->set('player2_result', $mod_game->get('player2_result') + $p2_result);


			$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
		}


		# сохранить результаты игроков (и в таблице игрока, и в таблице игры)
		$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
		$move_sequence->save();	# todo: BIG performance problem!!!

		$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
		$mod_payer1->save();	# todo: performance problem!!!
		$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
		$mod_payer2->save();	# todo: performance problem!!!

		$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
		$mod_game->save();
		$profiler->Tick('ctrTour::game_cycle', $profiler_tick_param);
	}
};
