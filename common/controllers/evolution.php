<?
include_once('common/controllers/tour.php');

class ctrEvolution	{
	protected $param;
	protected $tour;
	protected $slice;
	protected $tours_done;
	protected $tours_result;

	function __construct()	{
		$this->param = array();
		$this->tour  = new ctrTour();
		$this->slice = array();
	}

	function set_param($scope, $param, $value)	{
		if (($scope == 'strategies') and isset($value['param']) and $value['param'])	{
			$m = array();
			$ar_param = explode(';', $value['param']);

			foreach( $ar_param as & $pval )
				if (preg_match('|(\d+)-(\d+)|', $pval, $m))	{
					$r = rand($m[1], $m[2]);
					$pval = preg_replace('|\d+-\d+|', $r, $pval);
				}

			$value['param'] = implode(';', $ar_param);
		}

		$this->param[$scope][$param] = $value;
	}

	function init()	{
		$this->tour->set_param('price1',  $this->param['tour']['price1']);
		$this->tour->set_param('price2',  $this->param['tour']['price2']);
		$this->tour->set_param('result1', $this->param['tour']['result1']);
		$this->tour->set_param('result2', $this->param['tour']['result2']);
		$this->tour->set_param('noise_in',  $this->param['tour']['noise_in']);
		$this->tour->set_param('noise_out', $this->param['tour']['noise_out']);
		$this->tour->set_param('gamelen',  $this->param['tour']['gamelen']);
		$this->tour->set_param('gm_save',  $this->param['tour']['gm_save']);

		$this->tours_done = 0;
	}

	function step()	{
		$this->step_make_tour();
		$this->step_make_slice();
		$this->step_make_evolution();

		return $this->step_is_more();
	}

	function get_slice()	{
		return $this->slice;
	}


	protected function step_make_tour()	{
		foreach( $this->param['strategies'] as $num => $strat )
			$this->tour->set_player($num, $strat['strategy'], $strat['param']);

		$this->tour->run();

		$this->tour_result = $this->tour->get_results();

		$this->tours_done++;
	}

	protected function step_make_slice()	{
		$slice = array();

		foreach( $this->tour_result['rating'] as $place => $results )
			foreach( $results as $result )	{
				$str = $result['strategy'];
				$col = $result['color'];

				if (preg_match('| |', $str))	{
					$e = explode(' ', $str);
					$str = $e[0];
				}

				if (!isset($slice[$str]))	$slice[$str] = array();

				$slice[$str][] = $col;
			}

		ksort($slice);
		foreach( $slice as & $s )	sort($s);


		$slice2 = array();

		foreach( $slice as $s )
			foreach( $s as $col )
				$slice2[] = $col;

		$this->slice = $slice2;
	}

	protected function step_make_evolution()	{
		# удалить последних dropout стратегий, добавить первых clone + из списка strategy
		$var = $this->param['variance'];
		$str = & $this->param['strategies'];


		$drop_list  = $this->get_out_strategies($var['dropout'], 0);
		$clone_list = $this->get_out_strategies($var['clone'], 1);
		$new_list   = $var['strategy'];


		foreach( $clone_list as $ind )
			$new_list[] = $str[$ind];


		foreach( $drop_list as $key => $ind )
			$str[$ind] = $new_list[$key];
	}

	protected function get_out_strategies($num, $top)	{
		$result = array();
		$res_size = 0;

		$rating = $this->tour_result['rating'];
		$r_keys = array_keys($rating);

		if (!$top)	rsort($r_keys);


		for( $ind = 0; $res_size < $num; $ind++ )	{
			$r_part = $rating[$r_keys[$ind]];
			$rpSize = count($r_part);

			if ($num - $res_size <= $rpSize)	{
				$rpInd_max = $rpSize - 1;

				for( ; (($rpSize > 0) and ($num > $res_size)) ; )	{
					$i = rand(0, $rpInd_max);
					if (!isset($r_part[$i]))	continue;

					$result[] = $r_part[$i]['number'];
					$res_size++;
					unset($r_part[$i]);
				}
			}	else	{
				foreach( $r_part as $str )	{
					$result[] = $str['number'];
					$res_size++;
				}
			}
		}


		return $result;
	}

	protected function step_is_more()	{
		if ($this->tours_done < $this->param['lasting']['mintours'])	return true;

		return false;
	}
}
