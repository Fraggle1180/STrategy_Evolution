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
	protected $models;

	function __construct()	{
		$this->param	= array( 'price1' => null, 'price2' => null, 'result1' => null, 'result2' => null, 'noise_in' => null, 'noise_out' => null, 'gamelen' => null );
		$this->players	= array( 'max_num' => 0 );
		$this->data	= array( );
		$this->profiler = new fsb_profiler;
		$this->profiler->Tick('ctrTour::game_run');
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

	protected function run_init()	{
		# общие данные
		$this->stats	= array( 'time' => microtime(true), 'players' => 0, 'games' => 0, 'moves' => 0 );
		$this->models	= array( 'tour' => new modTour(), 'players' => new modPlayerInTournament(), 'games' => new modGame(), 'moves' => new modMove() );


		# параметры тура
		$mod_tour = & $this->models['tour'];
		$mod_tour->set_bulk(array('game_length' => $this->param['gamelen'], 'price1' => $this->param['price1'], 'price2' => $this->param['price2'],
						'result1' => $this->param['result1'], 'result2' => $this->param['result2'],
						'noise_in' => $this->param['noise_in'], 'noise_out' => $this->param['noise_out']));
		$mod_tour->save();

		$this->data['tour_id'] = $mod_tour->get('id');


		# параметры игроков
		$mod_players = & $this->models['players'];
		for( $p = 1; $p <= $this->players['max_num']; $p++ )	{
			if (!array_key_exists($p, $this->players) or is_null($this->players[$p]))	continue;

			$this->stats['players']++;


			$index = $mod_players->add();
			$this->players[$p]['index'] = $index;

			$st = $this->players[$p]['strategy'];
			$pr = $this->players[$p]['params'];
			$db = $st . ((is_null($pr) or !$pr) ? '' : " ($pr)");

			$mod_players->set_bulk($index, array( 'id_tournament' => $this->data['tour_id'], 'player_number' => $p, 'player_strategy' => $db, 'player_result' => 0 ));
		}

		$mod_players->save();

		$mod_tour->set('p_players', $this->stats['players']);


		# параметры игр
		$mod_games = & $this->models['games'];
		for( $p1 = 1; $p1 <= $this->players['max_num']; $p1++ )	{
			if (!array_key_exists($p1, $this->players) or is_null($this->players[$p1]))	continue;

			for( $p2 = 1; $p2 <= $this->players['max_num']; $p2++ )	{
				if (!array_key_exists($p2, $this->players) or is_null($this->players[$p2]))	continue;

				$index = $mod_games->add();
				$mod_games->set_bulk($index, array( 'id_tournament' => $this->data['tour_id'], 'player1_number' => $p1, 'player2_number' => $p2 ));
			}
		}

		$mod_games->save();
	}

	protected function run_play()	{
		$games_num = $this->models['games']->count();
		for( $g = 0; $g < $games_num; $g++ )
			$this->run_game($g);
	}

	protected function run_done()	{
		$this->models['players']->save();
		$this->models['games']->save();
		$this->models['moves']->save();


		$this->stats['time'] = microtime(true) - $this->stats['time'];
	}

	function run()	{
		$this->run_init();
		$this->run_play();
		$this->run_done();
	}

	function get_results()	{
		return array( 'rating' => modPlayerInTournament::getRating($this->data['tour_id']), 'stats' => $this->stats );
	}


	protected function run_game($game_ind)	{
		$this->profiler->Tick('ctrTour::game_run', '', 0);
		set_time_limit(300);

		$this->stats['games']++;


		# модель данных игры
		$mod_games = & $this->models['games'];
		$pl1 = $mod_games->get($game_ind, 'player1_number');
		$pl2 = $mod_games->get($game_ind, 'player2_number');
		$d_game_id = $mod_games->get($game_ind, 'id');


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
		$move_sequence	= new modMove();
		$mod_players	= & $this->models['players'];

		$player1	= $this->players[$pl1];
		$str_class1	= ctrStrategy::getClass_byName($player1['strategy']);
		$pl_strategy1	= new $str_class1($move_sequence, 1);
		$pl_strategy1->setParam($player1['params']);

		$player2	= $this->players[$pl2];
		$str_class2	= ctrStrategy::getClass_byName($player2['strategy']);
		$pl_strategy2	= new $str_class2($move_sequence, 2);
		$pl_strategy1->setParam($player1['params']);


		# моделировать игру
		# результат:
		#   1) в моделях игроков изменятся player_score в соотвествии с результатом этой игры
		#   2) добавятся ходы

		for ( $m = 1; $m <= $p_gamelen; $m++ )	{
		$this->profiler->Tick('ctrTour::game_run', '', 0);
			$this->stats['moves']++;

			$p1_decision = $pl_strategy1->MakeMove();
			$p2_decision = $pl_strategy2->MakeMove();
		$this->profiler->Tick('ctrTour::game_run');

			$p1_action   = (rand(1, 100) > $p_noise_in) ? $p1_decision : (1 - $p1_decision);
			$p2_action   = (rand(1, 100) > $p_noise_in) ? $p2_decision : (1 - $p2_decision);

			$p1_result   = - ($p1_action * $p_price1) + $p2_action * $p_result1;
			$p2_result   = - ($p2_action * $p_price2) + $p1_action * $p_result2;

			$p1_perception = (rand(1, 100) > $p_noise_out) ? $p2_action : (1 - $p2_action);
			$p2_perception = (rand(1, 100) > $p_noise_out) ? $p1_action : (1 - $p1_action);
		$this->profiler->Tick('ctrTour::game_run');


			$move_sequence->append(array( 'id_game' => $d_game_id, 'number_move' => $m, 'player1_decision' => $p1_decision, 'player2_decision' => $p2_decision, 'player1_action' => $p1_action, 'player2_action' => $p2_action, 'player1_perception' => $p1_perception, 'player2_perception' => $p2_perception));
		$this->profiler->Tick('ctrTour::game_run');


			$mod_players->set(($pl1-1), 'player_result', $mod_players->get(($pl1-1), 'player_result') + $p1_result);
			$mod_players->set(($pl2-1), 'player_result', $mod_players->get(($pl2-1), 'player_result') + $p2_result);
		$this->profiler->Tick('ctrTour::game_run');

			$mod_games->set($game_ind, 'player1_result', $mod_games->get($game_ind, 'player1_result') + $p1_result);
			$mod_games->set($game_ind, 'player2_result', $mod_games->get($game_ind, 'player2_result') + $p2_result);
		$this->profiler->Tick('ctrTour::game_run');
		}


		$this->profiler->Tick('ctrTour::game_run');
		# сохранить ходы в общий датасет
		$mod_moves	= & $this->models['moves'];
		$move_num	= $move_sequence->count();

		$mod_moves->tail($move_sequence);
		$this->profiler->Tick('ctrTour::game_run');
	}
};
